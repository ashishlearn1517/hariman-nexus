<script>
    function invoiceBuilder(existingRows = null) {
        return {
            rows: existingRows || [{ key: Date.now(), type: 'service', itemId: '', quantity: 1, rate: 0 }],
            taxRate: Number(document.getElementById('tax_setting_id')?.selectedOptions[0]?.dataset.rate || 0),
            serviceOptions: mergeInvoiceOptions(
                @js($services->map(fn ($service) => ['id' => (string) $service->id, 'label' => $service->short_name.' - '.$service->long_name, 'rate' => (float) $service->default_rate])->values()),
                @js($prefillServiceOptions ?? collect())
            ),
            productOptions: mergeInvoiceOptions(
                @js($products->map(fn ($product) => ['id' => (string) $product->id, 'label' => $product->product_code.' - '.$product->name, 'rate' => (float) $product->unit_price])->values()),
                @js($prefillProductOptions ?? collect())
            ),
            init() {
                this.rows = this.rows.map((row) => ({ ...row, itemSearch: row.itemSearch || this.selectedLabel(row) }));
            },
            get subtotal() {
                return this.rows.reduce((sum, row) => sum + (Number(row.quantity || 0) * Number(row.rate || 0)), 0);
            },
            get taxAmount() {
                return this.subtotal * this.taxRate / 100;
            },
            get total() {
                return this.subtotal + this.taxAmount;
            },
            addRow(type) {
                this.rows.push({ key: Date.now() + Math.random(), type, itemId: '', itemSearch: '', quantity: 1, rate: 0 });
            },
            removeRow(index) {
                if (this.rows.length > 1) {
                    this.rows.splice(index, 1);
                }
            },
            optionsFor(type) {
                return type === 'product' ? this.productOptions : this.serviceOptions;
            },
            syncRate(row) {
                const option = this.optionsFor(row.type).find((item) => String(item.id) === String(row.itemId));
                row.rate = option ? option.rate : 0;
                row.itemSearch = option ? option.label : '';
            },
            syncItemFromSearch(row) {
                const option = this.optionsFor(row.type).find((item) => item.label.toLowerCase() === String(row.itemSearch || '').toLowerCase());
                row.itemId = option ? String(option.id) : '';
                row.rate = option ? option.rate : 0;
            },
            selectedLabel(row) {
                const option = this.optionsFor(row.type).find((item) => String(item.id) === String(row.itemId));
                return option ? option.label : (row.label || '');
            },
            money(value) {
                return Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            },
        };
    }

    function mergeInvoiceOptions(baseOptions, prefillOptions) {
        const optionsById = new Map();

        [...baseOptions, ...prefillOptions].forEach((option) => {
            if (option.id !== null && option.id !== undefined && option.id !== '') {
                optionsById.set(String(option.id), { ...option, id: String(option.id) });
            }
        });

        return Array.from(optionsById.values());
    }
</script>
