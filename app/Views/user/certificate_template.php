<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sertifikat - <?= esc($course['title']) ?></title>
    <?php $supportsImages = $supportsImages ?? false; ?>
    <style>
        @page {
            margin: 0;
            size: 297mm 210mm;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
        }

        html,
        body {
            width: 297mm;
            min-height: 0;
            overflow: hidden;
        }

        body {
            background-color: #dff3ff;
            line-height: 1;
        }

        .page {
            width: 277mm;
            height: 190mm;
            margin: 10mm;
            padding: 0;
            overflow: hidden;
            background-color: #dff3ff;
            page-break-before: avoid;
            page-break-after: avoid;
            page-break-inside: avoid;
        }

        .certificate-wrapper {
            width: 277mm;
            height: 190mm;
            background-color: #f8fcff;
            <?php if ($supportsImages): ?>
            background-image: url("<?= esc($imagePath) ?>certificate-background.png");
            background-size: cover;
            background-position: center;
            <?php endif; ?>
            border-radius: 6px;
            position: relative;
            overflow: hidden;
            box-shadow: inset -20px -20px 60px #bfdbfe55;
        }

        .certificate-wrapper:before {
            content: "";
            position: absolute;
            top: 8mm;
            left: 8mm;
            right: 8mm;
            bottom: 8mm;
            border: 0.4mm solid #bfdbfe;
        }

        .certificate-wrapper:after {
            content: "";
            position: absolute;
            top: 12mm;
            left: 12mm;
            right: 12mm;
            bottom: 12mm;
            border: 0.25mm solid #93c5fd;
        }

        .certificate-header {
            text-align: center;
            position: relative;
            margin: 0;
            display: block;
            padding-top: 12mm;
            z-index: 2;
        }

        .certificate-title {
            font-family: Georgia, "Times New Roman", serif;
            font-size: 24pt;
            font-weight: 700;
            color: #4f46e5;
            text-transform: uppercase;
            margin-bottom: 5mm;
            line-height: 1.1;
        }

        .certificate-content {
            padding: 8mm 12mm 6mm 12mm;
            text-align: center;
            position: relative;
            margin: 0;
            z-index: 2;
        }

        .recipient-label {
            margin-top: 18mm;
            font-size: 11pt;
            color: #1e293b;
            margin-bottom: 4mm;
            font-weight: 400;
        }

        .recipient-name {
            font-family: Georgia, "Times New Roman", serif;
            font-size: 28pt;
            font-weight: 700;
            color: #4f46e5;
            margin-bottom: 8mm;
            display: inline-block;
            padding-bottom: 1mm;
            text-transform: capitalize;
        }

        .achievement-text {
            font-size: 10pt;
            color: #1e293b;
            margin-bottom: 4mm;
            font-weight: 400;
        }

        .course-title {
            font-family: Georgia, "Times New Roman", serif;
            font-size: 16pt;
            font-weight: 600;
            color: #4f46e5;
            margin-bottom: 8mm;
            padding: 0 6mm;
            line-height: 1.2;
        }

        .date-completed {
            font-size: 10pt;
            color: #1e293b;
            font-weight: 400;
            margin-bottom: 12mm;
        }

        .certificate-footer {
            position: absolute;
            bottom: 26mm;
            left: 12mm;
            right: 12mm;
            z-index: 2;
        }

        .signatures {
            width: 100%;
            margin-bottom: 8mm;
            text-align: center;
            display: table;
            table-layout: fixed;
        }

        .signature {
            display: table-cell;
            width: 33.333%;
            text-align: center;
            vertical-align: top;
        }

        .signature img {
            height: 24mm;
        }

        .signature-line {
            width: 42mm;
            margin: 18mm auto 2mm auto;
            border-top: 0.3mm solid #1e293b;
        }

        .signature-label {
            font-size: 8pt;
            color: #1e293b;
            font-weight: 500;
        }

        .certificate-number {
            font-size: 10pt;
            color: #1e293b;
            font-weight: 400;
            text-align: center;
            margin-top: 10mm;
        }

        .certificate-wrapper,
        .certificate-header,
        .certificate-content,
        .certificate-footer {
            page-break-inside: avoid;
            page-break-before: avoid;
            page-break-after: avoid;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="certificate-wrapper">
            <div class="certificate-header">
                <h1 class="certificate-title">Sertifikat Penyelesaian</h1>
            </div>

            <div class="certificate-content">
                <p class="recipient-label">Dengan ini menyatakan bahwa</p>
                <h2 class="recipient-name"><?= esc($user['name']) ?></h2>

                <p class="achievement-text">telah berhasil menyelesaikan kursus</p>

                <h3 class="course-title">"<?= esc($course['title']) ?>"</h3>

                <p class="date-completed">
                    Diselesaikan pada tanggal <strong><?= esc($completedDate) ?></strong>
                </p>

                <div class="certificate-footer">
                    <div class="signatures">
                        <div class="signature">
                            <?php if ($supportsImages): ?>
                                <img src="<?= esc($imagePath) ?>director-signature.png" alt="Tanda Tangan Instruktur">
                            <?php else: ?>
                                <div class="signature-line"></div>
                            <?php endif; ?>
                            <div class="signature-label">Instruktur Kursus</div>
                        </div>

                        <div class="signature">
                            <div class="certificate-number">
                                No. Sertifikat: <?= esc($certificateNumber) ?>
                            </div>
                        </div>

                        <div class="signature">
                            <?php if ($supportsImages): ?>
                                <img src="<?= esc($imagePath) ?>director-signature.png" alt="Tanda Tangan Direktur">
                            <?php else: ?>
                                <div class="signature-line"></div>
                            <?php endif; ?>
                            <div class="signature-label">Direktur Program</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
