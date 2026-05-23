<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        return view('sales.products.index', [
            'products' => Product::query()
                ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                    $query->where('product_code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%");
                }))
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'statuses' => Product::statusOptions(),
            'nextProductCode' => $this->nextProductCode(),
            'search' => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedProduct($request);

        DB::transaction(function () use ($validated): void {
            Product::create(array_merge($validated, [
                'product_code' => $this->nextProductCode(),
            ]));
        });

        return redirect()
            ->route('sales.products.index')
            ->with('status', 'product-created');
    }

    public function edit(Product $product): View
    {
        return view('sales.products.edit', [
            'product' => $product,
            'statuses' => Product::statusOptions(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $product->update($this->validatedProduct($request));

        return redirect()
            ->route('sales.products.index')
            ->with('status', 'product-saved');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedProduct(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:3000'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string', Rule::in(array_keys(Product::statusOptions()))],
        ]);
    }

    private function nextProductCode(): string
    {
        $highestNumber = 0;

        Product::query()
            ->select(['id', 'product_code'])
            ->lockForUpdate()
            ->get()
            ->each(function (Product $product) use (&$highestNumber): void {
                $number = $product->id;

                if (preg_match('/^PROD-(\d{4,})$/', $product->product_code, $matches)) {
                    $number = max($number, (int) $matches[1]);
                }

                $highestNumber = max($highestNumber, $number);
            });

        do {
            $highestNumber++;
            $productCode = 'PROD-'.str_pad((string) $highestNumber, 4, '0', STR_PAD_LEFT);
        } while (Product::query()->where('product_code', $productCode)->exists());

        return $productCode;
    }
}
