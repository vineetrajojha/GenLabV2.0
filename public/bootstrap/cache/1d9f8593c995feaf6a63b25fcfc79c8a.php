

<?php $__env->startSection('title', 'Invoice Report'); ?>

<?php $__env->startSection('content'); ?>


<?php if($errors->any()): ?>
<div class="alert alert-danger">
    <ul class="mb-0">
        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li><?php echo e($error); ?></li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>
</div>
<?php endif; ?>

<?php if(session('error')): ?>
<div class="alert alert-danger"><?php echo e(session('error')); ?></div>
<?php endif; ?>

<?php if(session('success')): ?>
<div class="alert alert-success"><?php echo e(session('success')); ?></div>
<?php endif; ?>



<style>
/* ================= A4 PAGE ================= */
.a4-page {
    width: 210mm;
    min-height: 297mm;
    margin: 0 auto;
    padding: 15mm;
    background: #fff;
    box-shadow: 0 0 10px rgba(0,0,0,0.15);
}

/* ================= INVOICE ================= */
.invoice-preview {
    font-family: 'Noto Sans', Arial, sans-serif;
    font-size: 12px;
    color: #333;
    line-height: 1.2;
}

.invoice-preview table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 10px;
    table-layout: fixed;
}

.invoice-preview th,
.invoice-preview td {
    border: 1px solid #000;
    padding: 6px 10px;
    font-size: 12px;
    word-wrap: break-word;
}

.invoice-preview th {
    background: #e9ecef;
    font-weight: bold;
}

/* ================= TEXT HELPERS ================= */
.invoice-preview .text-start { text-align: left; text-transform: uppercase; }
.invoice-preview .text-uppercase { text-transform: uppercase; }
.invoice-preview .text-right { text-align: right; }
.invoice-preview .text-centre { text-align: center; font-weight: bold; }
.invoice-preview .text-bottom { text-align: center; font-weight: bold; vertical-align: bottom; }
.invoice-preview .total-row { font-weight: bold; background: #f9f9f9; }

/* ================= COLUMN WIDTHS ================= */
.invoice-preview .col-left { width: 30%; }
.invoice-preview .col-wide { width: 52%; }

/* ================= EDITABLE FIELDS ================= */
.invoice-preview [contenteditable="true"] {
    background: #ffffff;
    cursor: text;
}

.invoice-preview [contenteditable="true"]:focus {
    outline: 2px solid #ffc107;
    background: #fff3a0;
}   

.item-row.selected {
    background: #fff3cd !important;
    outline: 2px solid #ffc107;
}

/* ================= PRINT ================= */
@media print {
    body { background: none; }
    .a4-page {
        box-shadow: none;
        margin: 0;
        padding: 15mm;
        width: 210mm;
        height: 297mm;
        page-break-after: always;
    }
}
</style>  


<div class="row"> 
    
    <div class="a4-page">
        <div class="invoice-preview">

            
            <table>
                <thead>
                    <tr>
                        <th class="col-left text-uppercase" contenteditable="true">
                            GSTIN: <?php echo e($invoiceData['bankDetails']['gstin'] ?? '9113464642541'); ?>

                        </th>

                        <th class="text-centre text-uppercase" colspan="2" contenteditable="true">
                            <?php echo e($invoiceData['invoice']['invoiceType'] ?? 'Tax Invoice'); ?>

                        </th>

                        <th class="text-centre">Scan to Pay</th>
                    </tr>
                </thead>

                <tbody>
                    
                    <tr>
                        <th class="col-left text-start">Bill Issue To:</th>

                        <td class="col-wide text-start text-uppercase" colspan="2" contenteditable="true">
                            <?php echo e($invoiceData['invoice']['bill_issue_to'] ?? ''); ?><br>
                            <?php echo nl2br(e($invoiceData['invoice']['address'] ?? '')); ?><br>
                            <span contenteditable="false" style="font-weight:bold;">
                                GSTIN:
                            </span> 
                            <?php echo e($invoiceData['invoice']['client_gstin'] ?? ''); ?>

                        </td>

                        <td class="text-centre">
                            <?php if(!empty($qrcode)): ?>
                                <img src="data:image/svg+xml;base64,<?php echo e($qrcode); ?>" width="100">
                            <?php endif; ?>
                        </td>
                    </tr>

                    
                    <tr>
                        <th class="text-start">Invoice No:</th>
                        <td colspan="3" class="text-uppercase" contenteditable="true">
                            <?php echo e($invoiceData['invoice']['invoice_no'] ?? ''); ?>

                        </td>
                    </tr>

                    <tr>
                        <th class="text-start">Invoice Date:</th>
                        <td colspan="3" contenteditable="true">
                            <?php echo e($invoiceData['invoice']['invoice_date'] ?? now()->format('d-m-Y')); ?>

                        </td>
                    </tr>

                    <tr>
                        <th class="text-start">Ref. No & Date:</th>
                        <td colspan="3" contenteditable="true">
                            <?php echo e($invoiceData['invoice']['ref_no'] ?? ''); ?>

                            <?php echo e($invoiceData['invoice']['ref_date'] ?? ''); ?>

                        </td>
                    </tr>

                    <tr>
                        <th class="text-start">Name of Work:</th>
                        <td colspan="3" contenteditable="true">
                            <?php echo e($invoiceData['invoice']['name_of_work'] ?? ''); ?>

                        </td>
                    </tr>
                </tbody>
            </table>


            
            <table >
                <thead>
                    <tr>
                        <!-- <th style="width:9%;">#</th> -->
                        <th style="width:35%;">Description</th>
                        <th style="width:20%;">Job Order No</th>
                        <th style="width:10%;">SAC Code</th>
                        <th style="width:10%;">Qty</th>
                        <th style="width:20%;">Rate</th>
                        <th style="width:25%;">Amount</th>
                    </tr>
                </thead>

                <tbody>
                <?php if($booking->items->isNotEmpty()): ?>
                    <?php $__currentLoopData = $booking->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="item-row">
                            <!-- <td contenteditable="true"><?php echo e($loop->iteration); ?></td> -->
                            <td contenteditable="true" class="editable description">
                                <?php echo e($item->sample_description); ?>

                            </td>
                            <td><?php echo e($item->job_order_no); ?></td>
                            <td><?php echo e($booking->sac_code ?? ''); ?></td>
                            <td contenteditable="true" class="editable qty">
                                <?php echo e($item->qty ?? 1); ?>

                            </td>
                            <td contenteditable="true" class="editable rate">
                                <?php echo e(number_format($item->amount, 2)); ?>

                            </td>
                            <td class="amount">0.00</td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php else: ?>
                    <?php for($i = 1; $i <= 9; $i++): ?>
                        <tr class="item-row">
                            <td><?php echo e($i); ?></td>
                            <td contenteditable="true" class="editable description"></td>
                            <td></td>
                            <td></td>
                            <td contenteditable="true" class="editable qty">1</td>
                            <td contenteditable="true" class="editable rate">0.00</td>
                            <td class="amount" contenteditable="true">0.00</td>
                        </tr>
                    <?php endfor; ?>
                <?php endif; ?>


                
                <tr class="total-row">
                        <td colspan="5" class="text-right">Total Amount</td>
                        <td id="totalAmount">0.00</td>
                    </tr>

                    <tr class="total-row" id="discountRow">
                        <td colspan="5" class="text-right">
                            Discount (
                            <span contenteditable="true"
                                id="discountPercent"
                                class="editable-percent">0</span> %)
                        </td>
                        <td id="discountAmount">0.00</td>
                    </tr>

                    <tr class="total-row" id="afterDiscountRow">
                        <td colspan="5" class="text-right">After Discount</td>
                        <td id="afterDiscount">0.00</td>
                    </tr>

                    <tr class="total-row">
                        <td colspan="5" class="text-right">
                            CGST (
                            <span contenteditable="true"
                                id="cgstPercent"
                                class="editable-percent">0</span> %)
                        </td>
                        <td id="cgstAmount">0.00</td>
                    </tr>

                    <tr class="total-row">
                        <td colspan="5" class="text-right">
                            SGST (
                            <span contenteditable="true"
                                id="sgstPercent"
                                class="editable-percent">0</span> %)
                        </td>
                        <td id="sgstAmount">0.00</td>
                    </tr>

                    <tr class="total-row">
                        <td colspan="5" class="text-right">
                            IGST (
                            <span contenteditable="true"
                                id="igstPercent"
                                class="editable-percent">0</span> %)
                        </td>
                        <td id="igstAmount">0.00</td>
                    </tr>

                    <tr class="total-row" id="roundOffRow">
                        <td colspan="5" class="text-right">Round Off</td>
                        <td id="roundOff">0.00</td>
                    </tr>

                    <tr class="total-row">
                        <td colspan="5" class="text-right">Payable Amount</td>
                        <td id="payableAmount">0.00</td>
                    </tr>

                    <tr>
                        <th colspan="6" id="amountInWords" class="text-centre">
                            Amount in Words:
                        </th>
                    </tr>

                </tbody>
            </table> 
            
            <!-- Bank Details -->
            <table class="bank-table">
                <tbody>
                    <tr>
                        <th class="text-start">INSTRUCTIONS:</th>
                        <td colspan="2"><?php echo e($bankInfo->instructions ?? ''); ?></td>
                    </tr>
                    <tr>
                        <th class="text-start">BANK NAME:</th>
                        <td><?php echo e($bankInfo->name ?? ''); ?></td>
                        <td class="text-centre text-uppercase">For <?php echo e($companyName ?? ''); ?></td>
                    </tr>
                    <tr>
                        <th class="text-start">ACCOUNT NO:</th>
                        <td><?php echo e($bankInfo->account_no ?? ''); ?></td>
                        <td rowspan="5" class="text-bottom">Authorised Signatory</td>
                    </tr>
                    <tr><th class="text-start">BRANCH:</th><td class="text-uppercase"><?php echo e($bankInfo->branch ?? ''); ?></td></tr>
                    <tr><th class="text-start">IFSC CODE:</th><td class="text-uppercase"><?php echo e($bankInfo->ifsc_code ?? ''); ?></td></tr>
                    <tr><th class="text-start">PAN NO:</th><td class="text-uppercase"><?php echo e($bankInfo->pan_no ?? ''); ?></td></tr>
                    <tr><th class="text-start">GSTIN:</th><td class="text-uppercase"><?php echo e($bankInfo->gstin ?? ''); ?></td></tr>
                </tbody>
            </table>

        </div>
    </div>

   
    <div class="col-lg-3">
        <div class="card shadow-sm position-sticky" style="top: 90px;">
            <div class="card-header fw-semibold">
                Invoice Settings
            </div>

            <div class="card-body">
                <div class="form-check mb-2">
                    <input class="form-check-input"
                           type="checkbox"
                           id="enableRoundOff"
                           checked>

                    <label class="form-check-label fw-semibold"
                           for="enableRoundOff">
                        Enable Round Off
                    </label>
                </div>  

                <div class="form-check mb-2">
                    <input class="form-check-input"
                           type="checkbox"
                           id="enableDiscount"
                           checked>

                    <label class="form-check-label fw-semibold"
                           for="enableDiscount">
                        Dicount Applicable
                    </label>
                </div>  

                <hr>

                <div class="fw-semibold mb-2">Item Row Actions</div>

                <button type="button"
                        class="btn btn-sm btn-outline-primary w-100 mb-2"
                        onclick="addRowAfterSelected()">
                    ➕ Add Row After
                </button>

                <button type="button"
                        class="btn btn-sm btn-outline-danger w-100"
                        onclick="removeSelectedRow()">
                    ❌ Remove Selected Row
                </button>
                
                <div class="mt-3 small text-muted">
                    💡 <strong>Tip:</strong> Select a row and press 
                    <kbd>Ctrl</kbd> + <kbd>M</kbd> to merge it into a single line.
                </div>
                <!-- future controls -->
                <!-- <hr>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox">
                    <label class="form-check-label">Show GST</label>
                </div> -->
            </div>
        </div>
    </div>
</div>





<script>
    function recalculateAll() {

        let totalAmount = 0;

        // ================= ITEM ROW CALC =================
        document.querySelectorAll('.invoice-preview tbody tr').forEach(row => {

            const qtyEl = row.querySelector('.qty');
            const rateEl = row.querySelector('.rate');
            const amountEl = row.querySelector('.amount');

            if (!qtyEl || !rateEl || !amountEl) return;

            const qty = parseFloat(qtyEl.innerText) || 0;
            const rate = parseFloat(rateEl.innerText.replace(/,/g, '')) || 0;

            const rowAmount = qty * rate;
            amountEl.innerText = rowAmount.toFixed(2);

            totalAmount += rowAmount;
        });

        // ================= TOTAL =================
        document.getElementById('totalAmount').innerText = totalAmount.toFixed(2);

        // ================= DISCOUNT =================
        // ================= DISCOUNT =================
        const enableDiscount =
            document.getElementById('enableDiscount')?.checked ?? true;

        let discountPercent = 0;
        let discountAmount = 0;
        let afterDiscount = totalAmount;

        if (enableDiscount) {

            document.getElementById('discountRow').style.display = '';
            document.getElementById('afterDiscountRow').style.display = '';

            discountPercent = parseFloat(
                document.getElementById('discountPercent').innerText
            ) || 0;

            discountAmount = (totalAmount * discountPercent) / 100;
            afterDiscount = totalAmount - discountAmount;

        } else {

            document.getElementById('discountRow').style.display = 'none';
            document.getElementById('afterDiscountRow').style.display = 'none';

            discountAmount = 0;
            afterDiscount = totalAmount;
        }

        document.getElementById('discountAmount').innerText =
            discountAmount.toFixed(2);

        document.getElementById('afterDiscount').innerText =
            afterDiscount.toFixed(2);

        // ================= GST =================
        const cgstPercent = parseFloat(document.getElementById('cgstPercent').innerText) || 0;
        const sgstPercent = parseFloat(document.getElementById('sgstPercent').innerText) || 0;
        const igstPercent = parseFloat(document.getElementById('igstPercent').innerText) || 0;

        const cgstAmount = (afterDiscount * cgstPercent) / 100;
        const sgstAmount = (afterDiscount * sgstPercent) / 100;
        const igstAmount = (afterDiscount * igstPercent) / 100;

        document.getElementById('cgstAmount').innerText = cgstAmount.toFixed(2);
        document.getElementById('sgstAmount').innerText = sgstAmount.toFixed(2);
        document.getElementById('igstAmount').innerText = igstAmount.toFixed(2);

        // ================= PAYABLE =================
        const enableRoundOff =
            document.getElementById('enableRoundOff')?.checked ?? true;

        let payable =
            afterDiscount + cgstAmount + sgstAmount + igstAmount;

        let finalPayable = payable;
        let roundOffValue = 0;

        if (enableRoundOff) {
            const roundedPayable = Math.round(payable);
            roundOffValue = roundedPayable - payable;
            finalPayable = roundedPayable;

            document.getElementById('roundOffRow').style.display = '';
        } else {
            document.getElementById('roundOffRow').style.display = 'none';
        }

        document.getElementById('roundOff').innerText =
            roundOffValue.toFixed(2);

        document.getElementById('payableAmount').innerText =
            finalPayable.toFixed(2);


        //  ALWAYS update words immediately
        updateAmountInWordsFromDOM();
    }
</script> 


<script>
    function numberToWords(num) {
        const ones = [
            '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six',
            'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve',
            'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
            'Seventeen', 'Eighteen', 'Nineteen'
        ];

        const tens = [
            '', '', 'Twenty', 'Thirty', 'Forty',
            'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'
        ];

        function convertBelowThousand(n) {
            let str = '';
            if (n >= 100) {
                str += ones[Math.floor(n / 100)] + ' Hundred ';
                n %= 100;
            }
            if (n >= 20) {
                str += tens[Math.floor(n / 10)] + ' ';
                n %= 10;
            }
            if (n > 0) {
                str += ones[n] + ' ';
            }
            return str.trim();
        }

        if (num === 0) return 'Zero';

        let words = '';
        if (num >= 10000000) {
            words += convertBelowThousand(Math.floor(num / 10000000)) + ' Crore ';
            num %= 10000000;
        }
        if (num >= 100000) {
            words += convertBelowThousand(Math.floor(num / 100000)) + ' Lakh ';
            num %= 100000;
        }
        if (num >= 1000) {
            words += convertBelowThousand(Math.floor(num / 1000)) + ' Thousand ';
            num %= 1000;
        }
        if (num > 0) {
            words += convertBelowThousand(num);
        }

        return words.trim();
    }
</script> 

<script>
    function updateAmountInWordsFromDOM() {

        const payableText =
            document.getElementById('payableAmount').innerText || '0';

        const amount = parseFloat(payableText) || 0;

        let rupees = Math.floor(amount);
        let paise = Math.round((amount - rupees) * 100);

        let words = rupees > 0
            ? numberToWords(rupees) + ' Rupees'
            : 'Zero Rupees';

        if (paise > 0) {
            words += ' and ' + numberToWords(paise) + ' Paise';
        }

        words += ' Only';

        document.getElementById('amountInWords').innerHTML =
            `<strong>Amount in Words:</strong> ${words}`;
    }
</script>

<script>
    ['input', 'keyup', 'blur'].forEach(evt => {
        document.querySelectorAll('.qty, .rate, .editable-percent').forEach(el => {
            el.addEventListener(evt, recalculateAll);
        });
    });

    // Initial load
    window.addEventListener('DOMContentLoaded', recalculateAll);
</script> 

<script>
    window.addEventListener('DOMContentLoaded', () => {
        recalculateAll();

        //  FORCE update words after render
        setTimeout(updateAmountInWordsFromDOM, 50);
    });
</script> 

<script>
    document.getElementById('enableRoundOff')
        .addEventListener('change', recalculateAll);
</script>


<script>
document.getElementById('enableDiscount')
    .addEventListener('change', recalculateAll);
</script>


<script>
    document.addEventListener('click', function (e) {
        const row = e.target.closest('.item-row');
        if (!row) return;

        document
            .querySelectorAll('.item-row')
            .forEach(r => r.classList.remove('selected'));

        row.classList.add('selected');
    });
</script> 


<script>
    function addRowAfterSelected() {
        const selected = document.querySelector('.item-row.selected');

        if (!selected) {
            alert('Please select a row first');
            return;
        }

        const newRow = document.createElement('tr');
        newRow.className = 'item-row';

        newRow.innerHTML = `
            <td contenteditable="true" class="editable description"></td>
            <td contenteditable="true"></td>
            <td contenteditable="true"></td>
            <td contenteditable="true" class="editable qty">1</td>
            <td contenteditable="true" class="editable rate">0.00</td>
            <td contenteditable="true" class="amount">0.00</td>
        `;

        selected.after(newRow);

        renumberRows();
        recalculateAll();
    }

    function removeSelectedRow() {
        const selected = document.querySelector('.item-row.selected');

        if (!selected) {
            alert('Please select a row to remove');
            return;
        }

        if (document.querySelectorAll('.item-row').length === 1) {
            alert('At least one item row is required');
            return;
        }

        selected.remove();

        renumberRows();
        recalculateAll();
    }

    function renumberRows() {
        document.querySelectorAll('.item-row').forEach((row, index) => {
            row.children[0].innerText = index + 1;
        });
    }
</script> 




<!--  GLOBAL INPUT LISTENER (PUT HERE) -->
<script>
    document.addEventListener('input', function (e) {
        if (
            e.target.classList.contains('qty') ||
            e.target.classList.contains('rate') ||
            e.target.classList.contains('editable-percent')
        ) {
            recalculateAll();
        }
    });
</script>


<script>
    window.addEventListener('DOMContentLoaded', () => {
        recalculateAll();
    });
</script>


<script>
    document.addEventListener('keydown', function (e) {

        // CTRL + M → Merge selected row
        if (e.ctrlKey && e.key.toLowerCase() === 'm') {
            e.preventDefault();
            mergeSelectedRow();
        }
    });
</script> 

<script>
    function mergeSelectedRow() {
        const row = document.querySelector('.item-row.selected');

        if (!row) {
            alert('Please select a row first');
            return;
        }

        // Prevent double merge
        if (row.dataset.merged === '1') return;

        const cells = row.children;

        // Build combined text from current columns
        const combinedText = `
    ${cells[0].innerText}
    Job: ${cells[1].innerText}
    SAC: ${cells[2].innerText}
    Qty: ${cells[3].innerText}
    Rate: ${cells[4].innerText}
    `.trim();

        // Save original row (for future undo)
        row.dataset.original = row.innerHTML;
        row.dataset.merged = '1';

        // Rebuild row:
        // - 1 combined column (Desc + Job + SAC + Qty + Rate)
        // - Amount column preserved
        row.innerHTML = `
            <td contenteditable="true"
                colspan="5"
                class="editable description">
                ${combinedText}
            </td>
            <td contenteditable="true" class="amount">${cells[5].innerText}</td>
        `;

        recalculateAll();
    }
</script>






<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH A:\GenTech\htdocs\GenlabV3.0\GenLabV3.0\resources\views/superadmin/accounts/generateInvoice/show.blade.php ENDPATH**/ ?>