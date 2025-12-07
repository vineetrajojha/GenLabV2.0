<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quotation PDF</title>
    <style>
        body {
            font-family: 'Noto Sans', Arial, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.4;
            /* padding: 20px 20px 0px 20px; bottom padding for footer */
            position: relative;
        }

        /* Header image */
        .header-img {
            width: 100%;
            position: fixed;
            top: 0;
            left: 0;
            height: 70px; /* adjust according to your image */
        }

        /* Footer image */
        .footer-img {
            width: 100%;
            position: fixed;
            bottom: 0;
            left: 0;
            height: 60px; /* adjust according to your image */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px 10px;
            font-size: 12px;
            word-wrap: break-word;
            overflow-wrap: break-word;
            hyphens: auto;
        }
        th { background: #e9ecef; font-weight: bold; }
        td { font-size: 12px; }
        .text-start { text-align: left; text-transform: uppercase; }
        .text-uppercase { text-transform: uppercase; }
        .text-right { text-align: right; }
        .text-centre { text-align: center; font-weight: bold; }
        .total-row { font-weight: bold; background: #f9f9f9; }
        .header-table td { border: none; padding: 2px 0; }
        .subject { margin-top: 20px; margin-bottom: 15px; }
        .terms { padding-top:100px; margin-top: 70px; font-size: 12px; }
        .terms h3 { font-size: 13px; font-weight: bold; margin-bottom: 8px; }
        .terms ol { padding-left: 18px; margin-bottom: 15px; }
        .terms p { margin: 4px 0; }
        .page-break {
            page-break-before: always; /* forces a new page before this element */
        }
    </style>
</head>
<body>

    <!-- Header Image -->
     <?php if($quotation->letterhead): ?>
        <img src="<?php echo e(public_path('assets/img/letterhead/hadding.png')); ?>" class="header-img">
     <?php endif; ?>

    <!-- Footer Image -->
     <?php if($quotation->letterhead): ?>
        <img src="<?php echo e(public_path('assets/img/letterhead/footer.png')); ?>" class="footer-img">
     <?php endif; ?>
    <!-- Header Section -->
    <table class="header-table" style="margin-top:80px;">
        <tr>
            <td><strong>REF:</strong> <?php echo e($quotation->quotation_no ?? '78787787878'); ?></td>
            <td class=""><strong>QUOTATION</strong></td>
        </tr>
        <tr>
            <td></td>
            <td class="text-right"><strong>Date:</strong> <?php echo e(\Carbon\Carbon::parse($quotation->date ?? now())->format('d-m-Y')); ?></td>
        </tr>
    </table>

    <p><strong>To,</strong><br>
        <strong><?php echo e($quotation->client_name ?? 'Avinash'); ?></strong><br>
        <?php echo nl2br($quotation->bill_issue_to ?? 'Avinajsjd'); ?><br>
        GST No: <?php echo e($quotation->client_gst ?? 'NA'); ?>

    </p>

    <div class="subject">
        <p><strong>Subject: Offer for Sample Analysis</strong></p>
        <p>Undersigned is here by submitting our most optimum rate for analysis of Samples.  
        Our offer is as follows:</p>
    </div>

    <!-- Items Table -->
    <table>
        <thead>
            <tr style="background:#e9ecef;">
                <th style="width:4%;">#</th>
                <th style="width:35%;">Sample/Parameter</th>
                <th style="width:10%;">Qty</th>
                <th style="width:12%;">Testing / Charges Qty. (Rs)</th>
                <th style="width:13%;">Total Testing Charges (Rs)</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($index + 1); ?></td>
                <td class="text-uppercase"><?php echo e($item['description'] ?? ''); ?></td>
                <td><?php echo e($item['qty'] ?? 1); ?></td>
                <td><?php echo e(number_format((float)($item['rate'] ?? 0), 2)); ?></td>
                <td><?php echo e(number_format((float)($item['amount'] ?? 0), 2)); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <tr class="total-row">
                <td colspan="4" class="text-right">Total Amount</td>
                <td><?php echo e(number_format((float)($quotation->total_amount ?? 0), 2)); ?></td>
            </tr>

            <?php if(($quotation->discount_amount ?? 0) > 0): ?>
            <tr class="total-row">
                <td colspan="4" class="text-right">Discount (<?php echo e($quotation->discount_percent ?? 0); ?>%)</td>
                <td><?php echo e(number_format((float)($quotation->discount_amount ?? 0), 2)); ?></td>
            </tr>
            <tr class="total-row">
                <td colspan="4" class="text-right">After Discount</td>
                <td><?php echo e(number_format((float)($quotation->after_discount_amount ?? 0), 2)); ?></td>
            </tr>
            <?php endif; ?>

            <tr class="total-row">
                <td colspan="4" class="text-right">CGST (<?php echo e($quotation->cgst_percent ?? 0); ?>%)</td>
                <td><?php echo e(number_format((float)($quotation->cgst_amount ?? 0), 2)); ?></td>
            </tr>
            <tr class="total-row">
                <td colspan="4" class="text-right">SGST (<?php echo e($quotation->sgst_percent ?? 0); ?>%)</td>
                <td><?php echo e(number_format((float)($quotation->sgst_amount ?? 0), 2)); ?></td>
            </tr>
            <tr class="total-row">
                <td colspan="4" class="text-right">IGST (<?php echo e($quotation->igst_percent ?? 0); ?>%)</td>
                <td><?php echo e(number_format((float)($quotation->igst_amount ?? 0), 2)); ?></td>
            </tr>
            <tr class="total-row">
                <td colspan="4" class="text-right">Round Off</td>
                <td><?php echo e(number_format((float)($quotation->round_off ?? 0), 2)); ?></td>
            </tr>
            <tr class="total-row">
                <td colspan="4" class="text-right">Payable Amount</td>
                <td><?php echo e(number_format((float)($quotation->payable_amount ?? 0), 2)); ?></td>
            </tr>
            <tr>
                <th colspan="5">Amount in Words: <?php echo e($WordAmout ?? ''); ?></th>
            </tr>
        </tbody>
    </table>

    <!-- Terms & Conditions Section -->
    <div class="terms page-break">
        <h3>Terms & Conditions:</h3>
        <ol>
            <li>The above quotation charges will be valid for next 30 days.</li>
            <li>Payment to be made 100% in advance. Cancellations / Refunds will not be accepted once booking has been made.</li>
            <li>
                Payment can be made in following modes: RTGS/NEFT Transfer<br><br>
                <table style="border:none; width:60%; margin-top:5px;">
                    <tr><td><strong>Bank</strong></td><td>: ICICI Bank</td></tr>
                    <tr><td><strong>Branch</strong></td><td>: Crossing Republic (GZB)</td></tr>
                    <tr><td><strong>Current A/c No.</strong></td><td>: 325405000561</td></tr>
                    <tr><td><strong>IFSC Code</strong></td><td>: ICIC0003254</td></tr>
                    <tr><td><strong>GSTIN</strong></td><td>: 09AAGFI2411P1Z6</td></tr>
                </table>
            </li>
            <li>Tax components are as per the regulatory norms by Government of India.</li>
            <li>Turnaround time is estimated considering usual workflow and it may change in case of bulk samples, technical discrepancies, or unforeseen circumstances.</li>
            <li>For more details & suggestions, please drop us a mail at 
                <a href="mailto:itlnoida.labs@gmail.com">itlnoida.labs@gmail.com</a>, 
                <a href="mailto:info@indiantestinglaboratory.com">info@indiantestinglaboratory.com</a>
            </li>
        </ol>
        <p><strong>Important Note:</strong> In case of payments made by NEFT/RTGS, please send us payment details (Company Name, Payment Date, Bill/Quotation No., Amount) at 
        <a href="mailto:itlnoida.labs@gmail.com">itlnoida.labs@gmail.com</a>, 
        <a href="mailto:info@indiantestinglaboratory.com">info@indiantestinglaboratory.com</a></p>

        <p>More details visit: <a href="http://www.indiantestinglaboratory.com">www.indiantestinglaboratory.com</a></p>

        <p><strong>Warm Regards</strong><br><br></p>
            <p class="text-uppercase">
        <em>(MANISH SINGH)</em><br>
        <?php echo e($companyName); ?><br>
        Mob. No: +91 - 8368595831, 9999669383
    </p>
    </div>

</body>
</html>
<?php /**PATH A:\GenTech\htdocs\GenlabV1.0\GenLabV1.0\resources\views/superadmin/accounts/quotation/quotation_pdf.blade.php ENDPATH**/ ?>