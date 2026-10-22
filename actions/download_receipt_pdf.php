    <?php
    // actions/download_receipt_pdf.php
    // Generates and downloads a clean, official PDF receipt for the customer's reservation.
    require_once __DIR__ . '/../includes/functions.php';
    $pdo = getDBConnection();
    $customerId = customer_user_id();

    if ($customerId <= 0 && !is_admin()) {
    require_customer();
    }

    $ref = trim($_GET['ref'] ?? '');
    if ($customerId > 0) {
    $st = $pdo->prepare("SELECT r.*, v.make, v.model, v.plate_number, v.daily_rate, v.seats, v.transmission, v.fuel_type
                            FROM reservations r
                            JOIN vehicles v ON r.vehicle_id = v.id
                            WHERE r.reference_code = ? AND r.user_id = ?");
    $st->execute([$ref, $customerId]);
    } else {
    $st = $pdo->prepare("SELECT r.*, v.make, v.model, v.plate_number, v.daily_rate, v.seats, v.transmission, v.fuel_type
                            FROM reservations r
                            JOIN vehicles v ON r.vehicle_id = v.id
                            WHERE r.reference_code = ?");
    $st->execute([$ref]);
    }
    $b = $st->fetch();

    if (!$b) {
    flash_set('error', 'Booking not found.');
    redirect('customer/history.php');
    }

    class ReceiptPDF {
    private $buffer = '';
    private $offsets = [];
    private $pageContent = '';

    public function __construct() {
        $this->buffer = "%PDF-1.4\n";
    }

    private function newObject($id, $content) {
        $this->offsets[$id] = strlen($this->buffer);
        $this->buffer .= "$id 0 obj\n" . $content . "\nendobj\n";
    }

    public function addText($x, $y, $text, $size = 11, $font = 'F1') {
        $clean = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
        // Replace unicode peso sign with PHP / Ps
        $clean = str_replace(['₱', '•', '–', '—', '&bull;', '&times;'], ['PHP ', '-', '-', '-', '-', 'x'], $clean);
        $this->pageContent .= "BT /$font $size Tf $x $y Td ($clean) Tj ET\n";
    }

    public function addLine($x1, $y1, $x2, $y2, $width = 1) {
        $this->pageContent .= "$width w $x1 $y1 m $x2 $y2 l S\n";
    }

    public function addRect($x, $y, $w, $h, $fill = false) {
        $this->pageContent .= "$x $y $w $h " . ($fill ? "re f\n" : "re S\n");
    }

    public function output() {
        $streamLen = strlen($this->pageContent);
        $this->newObject(4, "<< /Length $streamLen >>\nstream\n" . $this->pageContent . "endstream");
        $this->newObject(1, "<< /Type /Catalog /Pages 2 0 R >>");
        $this->newObject(2, "<< /Type /Pages /Kids [3 0 R] /Count 1 >>");
        $this->newObject(3, "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> /F2 << /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >> /F3 << /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Oblique >> >> >> >>");

        $xrefOffset = strlen($this->buffer);
        $this->buffer .= "xref\n0 5\n0000000000 65535 f \n";
        for ($i = 1; $i <= 4; $i++) {
            $this->buffer .= sprintf("%010d 00000 n \n", $this->offsets[$i]);
        }
        $this->buffer .= "trailer\n<< /Size 5 /Root 1 0 R >>\nstartxref\n$xrefOffset\n%%EOF";
        return $this->buffer;
    }
    }

    $pdf = new ReceiptPDF();

    // Header Branding
    $pdf->addText(40, 790, "DRIVESPHERE PHILIPPINES", 18, 'F2');
    $pdf->addText(40, 775, "Official Customer Vehicle Rental Receipt & Reservation Voucher", 10, 'F1');
    $pdf->addLine(40, 765, 555, 765, 1.5);

    // Reference & Meta Box
    $pdf->addText(40, 745, "RENTAL REFERENCE:", 9, 'F2');
    $pdf->addText(170, 745, $b['reference_code'], 10, 'F1');

    $pdf->addText(40, 730, "DATE ISSUED:", 9, 'F2');
    $pdf->addText(170, 730, date('F d, Y - h:i A', strtotime($b['created_at'])), 10, 'F1');

    $pdf->addText(40, 715, "CUSTOMER NAME:", 9, 'F2');
    $pdf->addText(170, 715, $b['driver_name'], 10, 'F1');

    $pdf->addText(40, 700, "RESERVATION STATUS:", 9, 'F2');
    $pdf->addText(170, 700, strtoupper($b['status']), 10, 'F2');

    $pdf->addLine(40, 688, 555, 688, 0.5);

    // Vehicle Details
    $pdf->addText(40, 670, "FLEET VEHICLE SPECIFICATIONS", 12, 'F2');
    $pdf->addText(40, 650, "Vehicle Model:", 10, 'F2');
    $pdf->addText(170, 650, $b['make'] . ' ' . $b['model'], 10, 'F1');

    $pdf->addText(40, 635, "License Plate:", 10, 'F2');
    $pdf->addText(170, 635, $b['plate_number'] ?: 'Verified Fleet Unit', 10, 'F1');

    $pdf->addText(40, 620, "Configuration:", 10, 'F2');
    $pdf->addText(170, 620, $b['transmission'] . " | " . $b['seats'] . " Seats | " . $b['fuel_type'], 10, 'F1');

    $pdf->addLine(40, 608, 555, 608, 0.5);

    // Itinerary
    $pdf->addText(40, 590, "TRIP ITINERARY & HANDOVER SCHEDULE", 12, 'F2');
    $pdf->addText(40, 570, "Pick-up Date & Time:", 10, 'F2');
    $pdf->addText(170, 570, date('F d, Y - h:i A', strtotime($b['pickup_date'])), 10, 'F1');

    $pdf->addText(40, 555, "Pick-up Hub Location:", 10, 'F2');
    $pdf->addText(170, 555, $b['pickup_location'], 10, 'F1');

    $pdf->addText(40, 540, "Return Date & Time:", 10, 'F2');
    $pdf->addText(170, 540, date('F d, Y - h:i A', strtotime($b['return_date'])), 10, 'F1');

    $pdf->addText(40, 525, "Return Hub Location:", 10, 'F2');
    $pdf->addText(170, 525, $b['return_location'], 10, 'F1');

    $pdf->addText(40, 510, "Total Duration:", 10, 'F2');
    $pdf->addText(170, 510, ((int)$b['days']) . " Day(s)", 10, 'F1');

    $pdf->addLine(40, 498, 555, 498, 0.5);

    // Fiscal Breakdown
    $pdf->addText(40, 480, "FISCAL BREAKDOWN & CHARGES (PHP)", 12, 'F2');

    $pdf->addText(40, 460, "Base Rental Charge (" . ((int)$b['days']) . " Days @ PHP " . number_format($b['daily_rate'], 2) . "):", 10, 'F1');
    $pdf->addText(450, 460, "PHP " . number_format($b['base_amount'], 2), 10, 'F2');

    $pdf->addText(40, 445, "Mandatory Government VAT (12%):", 10, 'F1');
    $pdf->addText(450, 445, "PHP " . number_format($b['vat_amount'], 2), 10, 'F2');

    $pdf->addText(40, 430, "Pre-authorized Refundable Security Deposit:", 10, 'F1');
    $pdf->addText(450, 430, "PHP " . number_format($b['deposit_amount'], 2), 10, 'F2');

    $pdf->addLine(40, 418, 555, 418, 1);

    $pdf->addText(40, 400, "TOTAL PAYABLE NET AMOUNT:", 12, 'F2');
    $pdf->addText(440, 400, "PHP " . number_format($b['total_price'], 2), 13, 'F2');

    // Footer notes
    $pdf->addLine(40, 380, 555, 380, 0.5);
    $pdf->addText(40, 360, "TERMS & CONDITIONS SUMMARY:", 9, 'F2');
    $pdf->addText(40, 345, "- Primary driver must present a valid Philippine LTO license or IDP at station vehicle handover.", 8.5, 'F1');
    $pdf->addText(40, 332, "- Vehicles are delivered with a full tank of fuel and must be surrendered with an equivalent level.", 8.5, 'F1');
    $pdf->addText(40, 319, "- Refundable security deposit is released within 24 hours of vehicle return check-in inspection.", 8.5, 'F1');
    $pdf->addText(40, 306, "- 24/7 Dedicated Customer Support and Fleet Command Hotline: (02) 8888-DRIVE.", 8.5, 'F1');

    $pdf->addText(40, 260, "Generated by DriveSphere Fleet System on " . date('Y-m-d H:i:s') . " - Authentic Digital Copy", 8, 'F3');

    $output = $pdf->output();

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="DriveSphere_Receipt_' . $b['reference_code'] . '.pdf"');
    header('Content-Length: ' . strlen($output));
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    echo $output;
    exit;
