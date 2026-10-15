<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ItemStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemImage;
use App\Services\ImageResizeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

class ItemController extends Controller
{
    public function __construct(
        protected ImageResizeService $imageResizeService
    ) {}

    public function index(Request $request): View
    {
        $query = Item::with(['category', 'images'])
            ->withCount('bookingItems');

        if ($request->filled('q')) {
            $search = $request->string('q');
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $items = $query->latest()->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('admin.items.index', compact('items', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::active()->orderBy('name')->get();
        return view('admin.items.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:items,slug'],
            'description' => ['nullable', 'string'],
            'price_per_day' => ['required', 'integer', 'min:0'],
            'deposit' => ['required', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:1'],
            'status' => ['required', new Enum(ItemStatus::class)],
            'specs_keys' => ['nullable', 'array'],
            'specs_values' => ['nullable', 'array'],
            'images.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'], // 5MB
        ], [
            'category_id.required' => 'Kategori unit wajib dipilih.',
            'name.required' => 'Nama unit wajib diisi.',
            'price_per_day.required' => 'Harga sewa per hari wajib diisi.',
            'deposit.required' => 'Deposit jaminan wajib diisi.',
            'stock.required' => 'Jumlah stok unit wajib diisi.',
            'images.*.image' => 'File foto harus berformat gambar.',
        ]);

        $slug = ($validated['slug'] ?? null) ?: Str::slug($validated['name']);
        $originalSlug = $slug;
        $counter = 1;
        while (Item::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        // Build specs json array
        $specs = [];
        if (! empty($validated['specs_keys']) && ! empty($validated['specs_values'])) {
            foreach ($validated['specs_keys'] as $idx => $key) {
                $val = $validated['specs_values'][$idx] ?? '';
                if (trim($key) !== '' && trim($val) !== '') {
                    $specs[trim($key)] = trim($val);
                }
            }
        }

        DB::transaction(function () use ($request, $validated, $slug, $specs) {
            $item = Item::create([
                'category_id' => $validated['category_id'],
                'name' => $validated['name'],
                'slug' => $slug,
                'description' => $validated['description'] ?? null,
                'price_per_day' => $validated['price_per_day'],
                'deposit' => $validated['deposit'],
                'stock' => $validated['stock'],
                'specs' => empty($specs) ? null : $specs,
                'status' => $validated['status'],
            ]);

            // Handle image uploads
            if ($request->hasFile('images')) {
                $isFirst = true;
                foreach ($request->file('images') as $file) {
                    $path = $this->imageResizeService->storeItemImage($file);
                    ItemImage::create([
                        'item_id' => $item->id,
                        'path' => $path,
                        'sort_order' => 0,
                        'is_primary' => $isFirst,
                    ]);
                    $isFirst = false;
                }
            }
        });

        return redirect()->route('admin.items.index')
            ->with('success', 'Unit rental berhasil ditambahkan.');
    }

    public function edit(Item $item): View
    {
        $categories = Category::orderBy('name')->get();
        $item->load('images');
        return view('admin.items.edit', compact('item', 'categories'));
    }

    public function update(Request $request, Item $item): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:items,slug,' . $item->id],
            'description' => ['nullable', 'string'],
            'price_per_day' => ['required', 'integer', 'min:0'],
            'deposit' => ['required', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:1'],
            'status' => ['required', new Enum(ItemStatus::class)],
            'specs_keys' => ['nullable', 'array'],
            'specs_values' => ['nullable', 'array'],
            'images.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        $slug = ($validated['slug'] ?? null) ?: Str::slug($validated['name']);
        if ($slug !== $item->slug) {
            $originalSlug = $slug;
            $counter = 1;
            while (Item::where('slug', $slug)->where('id', '!=', $item->id)->exists()) {
                $slug = $originalSlug . '-' . $counter++;
            }
        }

        $specs = [];
        if (! empty($validated['specs_keys']) && ! empty($validated['specs_values'])) {
            foreach ($validated['specs_keys'] as $idx => $key) {
                $val = $validated['specs_values'][$idx] ?? '';
                if (trim($key) !== '' && trim($val) !== '') {
                    $specs[trim($key)] = trim($val);
                }
            }
        }

        DB::transaction(function () use ($request, $item, $validated, $slug, $specs) {
            $item->update([
                'category_id' => $validated['category_id'],
                'name' => $validated['name'],
                'slug' => $slug,
                'description' => $validated['description'] ?? null,
                'price_per_day' => $validated['price_per_day'],
                'deposit' => $validated['deposit'],
                'stock' => $validated['stock'],
                'specs' => empty($specs) ? null : $specs,
                'status' => $validated['status'],
            ]);

            // Append additional images
            if ($request->hasFile('images')) {
                $hasPrimary = $item->images()->where('is_primary', true)->exists();
                foreach ($request->file('images') as $file) {
                    $path = $this->imageResizeService->storeItemImage($file);
                    ItemImage::create([
                        'item_id' => $item->id,
                        'path' => $path,
                        'sort_order' => 0,
                        'is_primary' => ! $hasPrimary,
                    ]);
                    $hasPrimary = true;
                }
            }
        });

        return redirect()->route('admin.items.index')
            ->with('success', 'Data unit berhasil diperbarui.');
    }

    public function setPrimaryImage(Item $item, ItemImage $image): RedirectResponse
    {
        if ($image->item_id !== $item->id) {
            abort(403);
        }

        $item->images()->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);

        return back()->with('success', 'Foto utama unit berhasil diperbarui.');
    }

    public function destroyImage(Item $item, ItemImage $image): RedirectResponse
    {
        if ($image->item_id !== $item->id) {
            abort(403);
        }

        $wasPrimary = $image->is_primary;

        if (! str_starts_with($image->path, 'http')) {
            Storage::disk('public')->delete($image->path);
        }

        $image->delete();

        if ($wasPrimary) {
            $nextImage = $item->images()->first();
            if ($nextImage) {
                $nextImage->update(['is_primary' => true]);
            }
        }

        return back()->with('success', 'Foto berhasil dihapus.');
    }

    public function destroy(Item $item): RedirectResponse
    {
        // Check active bookings
        $hasActiveBookings = $item->bookingItems()
            ->whereHas('booking', function ($q) {
                $q->whereIn('status', ['pending', 'confirmed', 'picked_up']);
            })
            ->exists();

        if ($hasActiveBookings) {
            return back()->with('error', 'Unit tidak dapat dihapus karena masih terikat dalam pesanan sewa aktif.');
        }

        $item->delete();

        return redirect()->route('admin.items.index')
            ->with('success', 'Unit rental berhasil dihapus.');
    }
}
