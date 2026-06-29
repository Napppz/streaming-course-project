<?php

namespace App\Controllers\Users;

use App\Controllers\BaseController;
use App\Models\CourseModel;
use App\Models\UserModel;
use App\Models\EnrollmentModel;
use Dompdf\Dompdf;
use Dompdf\Options;
use Config\Dompdf as DompdfConfig;

class CertificateController extends BaseController
{
    protected $courseModel;
    protected $userModel;
    protected $enrollmentModel;

    public function __construct()
    {
        $this->courseModel = new CourseModel();
        $this->userModel = new UserModel();
        $this->enrollmentModel = new EnrollmentModel();
    }

    public function generate($courseId)
    {
        // Tingkatkan batas memori
        ini_set('memory_limit', '768M');
        
        // Pastikan user sudah login
        if (!session()->has('id')) {
            return redirect()->to('/login')->with('error', 'Anda harus login terlebih dahulu.');
        }

        $userId = session()->get('id');

        // Cek apakah user telah menyelesaikan kursus
        $enrollment = $this->enrollmentModel->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        if (!$enrollment) {
            return redirect()->back()->with('error', 'Anda belum terdaftar di kursus ini.');
        }

        if ($enrollment['progress_percentage'] < 100) {
            return redirect()->back()->with('error', 'Anda belum menyelesaikan kursus ini.');
        }

        // Ambil data kursus
        $course = $this->courseModel->find($courseId);
        if (!$course) {
            return redirect()->back()->with('error', 'Kursus tidak ditemukan.');
        }

        // Ambil data user
        $user = $this->userModel->find($userId);
        if (!$user) {
            return redirect()->back()->with('error', 'Data pengguna tidak ditemukan.');
        }

        // Pastikan ada field name atau gunakan full_name
        if (!isset($user['name']) && isset($user['full_name'])) {
            $user['name'] = $user['full_name'];
        } else if (!isset($user['name']) && !isset($user['full_name'])) {
            // Fallback ke username atau email jika tidak ada nama
            $user['name'] = $user['username'] ?? $user['email'] ?? 'Student';
        }

        // Ambil waktu selesai kursus
        $completedDate = date('d F Y', strtotime($enrollment['completed_at'] ?? date('Y-m-d')));

        // Generate sertifikat
        return $this->generatePDF($user, $course, $completedDate);
    }

    private function generatePDF($user, $course, $completedDate)
    {
        try {
            // Ambil konfigurasi dari Config\Dompdf
            $config = new DompdfConfig();
            
            // Konfigurasi Dompdf
            $options = new Options();
            
            // Terapkan semua opsi dari konfigurasi
            foreach ($config->options as $key => $value) {
                $options->set($key, $value);
            }
            
            // Aktifkan akses remote
            $options->set('isRemoteEnabled', true);
            
            // Pastikan direktori font dan cache sudah ada
            if (!is_dir($config->options['font_dir'])) {
                mkdir($config->options['font_dir'], 0755, true);
            }
            if (!is_dir($config->options['temp_dir'])) {
                mkdir($config->options['temp_dir'], 0755, true);
            }

            // Inisialisasi Dompdf
            $dompdf = new Dompdf($options);
            
            // Landscape orientation untuk sertifikat
            $dompdf->setPaper('A4', 'landscape');

            // Generate nomor sertifikat unik
            $certificateNumber = 'CERT-' . strtoupper(substr(md5($user['id'] . $course['id'] . time()), 0, 8));
            
            // Siapkan data tambahan untuk template
            $viewData = [
                'user' => $user,
                'course' => $course,
                'completedDate' => $completedDate,
                'certificateNumber' => $certificateNumber,
                'imagePath' => $this->assetPathForPdf('assets/images/'),
                'supportsImages' => extension_loaded('gd')
            ];

            // Konten HTML untuk sertifikat
            $html = view('user/certificate_template', $viewData);

            // Load HTML ke Dompdf
            $dompdf->loadHtml($html);

            // Render PDF dengan opsi caching
            $dompdf->render();

            $filename = $this->safeCertificateFilename((string) ($course['slug'] ?? $course['title'] ?? 'sertifikat'));
            $pdf = $dompdf->output();

            return $this->response
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
                ->setBody($pdf);

        } catch (\Throwable $e) {
            // Log error
            log_message('error', 'Error generating PDF: ' . $e->getMessage());

            // Tampilkan pesan error
            return redirect()->back()->with('error', 'Gagal membuat sertifikat PDF: ' . $e->getMessage());
        }
    }

    private function assetPathForPdf(string $path): string
    {
        return str_replace('\\', '/', FCPATH . ltrim($path, '/'));
    }

    private function safeCertificateFilename(string $name): string
    {
        $filename = preg_replace('/[^A-Za-z0-9\-_]+/', '-', strtolower($name));
        $filename = trim((string) $filename, '-');

        return 'sertifikat-' . ($filename ?: 'kursus') . '.pdf';
    }
}
