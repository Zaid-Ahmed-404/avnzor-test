

function addRow(savedData = null) {
    const tbody = document.getElementById('itemRows');
    const row = document.createElement('tr');

    const optionsHtml = productsFromDB.map(product => {
        const isSelected = (savedData && savedData.id == product.id) ? 'selected' : '';
        return `
            <option value="${product.id}" 
                data-cost="${product.sale_price}" 
                data-vat-app="${product.is_vat_applicable}" 
                data-vat-per="${product.vat_percent}"
                ${isSelected}>
                ${product.name} (${product.code})
            </option>`;
    }).join('');

    row.innerHTML = `
        <td>
            <select name="product_id[]" class="form-select product-select" onchange="handleProductChange(this)" required>
                <option value="">Choose...</option>
                ${optionsHtml}
            </select>
        </td>
        <td>
            <input type="number" name="cost[]" class="form-control cost-input" step="0.01" 
                value="${savedData ? savedData.cost : 0}" oninput="calculateTotals()">
        </td>
        <td>
            <input type="number" name="quantity[]" class="form-control quantity-input" min="1" 
                value="${savedData ? savedData.quantity : 1}" oninput="calculateTotals()">
        </td>
        <td><span class="vat-percent-text text-muted">0%</span></td>
        <td><span class="vat-amount-text text-muted">SAR 0.00</span></td>
        <td class="fw-bold line-total">SAR 0.00</td>
        <td>
            <button type="button" class="btn btn-outline-danger btn-sm border-0 remove-btn" 
                    onclick="removeRow(this)" title="Remove Item">
                <i class="bi bi-trash3"></i>
            </button>
        </td>`;

    tbody.appendChild(row);

    if (savedData) {
        const select = row.querySelector('.product-select');
        handleProductChange(select, false);
    }
}

function handleProductChange(select, shouldSave = true) {
    const row = select.closest('tr');
    const option = select.options[select.selectedIndex];

    if (option && option.value) {

        if (shouldSave) {
            row.querySelector('.cost-input').value = option.dataset.cost;
        }

        const isVat = option.dataset.vatApp == '1';
        const vatPer = isVat ? option.dataset.vatPer : 0;
        row.querySelector('.vat-percent-text').innerText = vatPer + '%';
    } else {

        row.querySelector('.cost-input').value = 0;
        row.querySelector('.vat-percent-text').innerText = '0%';
    }

    calculateTotals(shouldSave);
}

function removeRow(btn) {
    btn.closest('tr').remove();
    calculateTotals();
}

function calculateTotals(shouldSave = true) {
    let net = 0;
    let totalVat = 0;

    const rows = document.querySelectorAll('#itemRows tr');

    rows.forEach(row => {
        const select = row.querySelector('.product-select');
        const option = select.options[select.selectedIndex];

        const cost = parseFloat(row.querySelector('.cost-input').value) || 0;
        const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;

        let vatRate = 0;
        if (option && option.value && option.dataset.vatApp == '1') {
            vatRate = parseFloat(option.dataset.vatPer) || 0;
        }

        const subtotal = cost * quantity;
        const vatAmount = subtotal * (vatRate / 100);
        const lineTotal = subtotal + vatAmount;


        row.querySelector('.vat-percent-text').innerText = vatRate + '%';
        row.querySelector('.vat-amount-text').innerText = 'SAR ' + vatAmount.toFixed(2);
        row.querySelector('.line-total').innerText = 'SAR ' + lineTotal.toFixed(2);

        net += subtotal;
        totalVat += vatAmount;
    });


    document.getElementById('displayNet').innerText = 'SAR ' + net.toFixed(2);
    document.getElementById('displayVat').innerText = 'SAR ' + totalVat.toFixed(2);
    document.getElementById('displayGrand').innerText = 'SAR ' + (net + totalVat).toFixed(2);

    if (shouldSave) {
        saveState();
    }
}

function saveState() {
    const items = [];
    document.querySelectorAll('#itemRows tr').forEach(row => {
        const id = row.querySelector('.product-select').value;
        if (id) {
            items.push({
                id: id,
                cost: row.querySelector('.cost-input').value,
                quantity: row.querySelector('.quantity-input').value
            });
        }
    });

    const state = {
        date: document.getElementById('purchase_date')?.value || '',
        supplier: document.getElementById('supplier')?.value || '',
        items: items
    };

    localStorage.setItem('draft_purchase', JSON.stringify(state));
}

function clearDraft() {
    localStorage.removeItem('draft_purchase');
    location.reload();
}

window.addEventListener('load', () => {
    const savedJson = localStorage.getItem('draft_purchase');

    if (savedJson) {
        try {
            const saved = JSON.parse(savedJson);

            if (saved.date && document.getElementById('purchase_date')) {
                document.getElementById('purchase_date').value = saved.date;
            }

            if (saved.supplier && document.getElementById('supplier')) {
                document.getElementById('supplier').value = saved.supplier;
            }

            if (saved.items && saved.items.length > 0) {
                saved.items.forEach(item => addRow(item));
            } else {
                addRow();
            }
        } catch (e) {
            console.error("Error parsing saved draft:", e);
            addRow();
        }
    } else {
        addRow();
    }
});


const purchaseForm = document.getElementById('purchaseForm');
if (purchaseForm) {
    purchaseForm.onsubmit = () => {
        localStorage.removeItem('draft_purchase');
    };
}