<?php

namespace Database\Seeders;

use App\Models\Notice;
use App\Models\NoticeAttachment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class NewsEventsSeeder extends Seeder
{
    public function run(): void
    {
        // Get the first admin user to be the author
        $admin = User::role('principal')->first();
        
        if (!$admin) {
            $this->command->error('No admin user found. Please run DatabaseSeeder first.');
            return;
        }

        // Create storage directories if they don't exist
        $storageDir = storage_path('app/public/notices');
        if (!File::exists($storageDir)) {
            File::makeDirectory($storageDir, 0755, true);
        }

        // Sample news and events data
        $newsEvents = [
            [
                'type' => 'news',
                'title' => 'MMP Wins National Technical Education Excellence Award 2081',
                'content' => '<p>Manmohan Memorial Polytechnic has been honored with the prestigious National Technical Education Excellence Award 2081 by the Council for Technical Education and Vocational Training (CTEVT).</p><p>The award recognizes our institution\'s outstanding contribution to technical education in Nepal, innovative teaching methodologies, and excellent student placement records.</p><p>Principal Mr. Binay Mahato received the award at a ceremony held in Kathmandu, attended by education ministers and CTEVT officials.</p>',
                'images' => ['award-ceremony.jpg', 'principal-award.jpg'],
                'videos' => [],
                'documents' => ['award-certificate.pdf'],
            ],
            [
                'type' => 'event',
                'title' => 'Annual Tech Fest 2081 - Innovation & Creativity',
                'content' => '<p>Join us for the Annual Tech Fest 2081, a three-day celebration of innovation, creativity, and technical excellence!</p><p><strong>Date:</strong> Jestha 15-17, 2081<br><strong>Venue:</strong> MMP Campus, Budhiganga-04</p><p>Events include: Project exhibitions, coding competitions, robotics demonstrations, technical workshops, and cultural programs.</p><p>All students, parents, and tech enthusiasts are welcome!</p>',
                'images' => ['tech-fest-poster.jpg', 'tech-fest-2080.jpg', 'robotics-demo.jpg'],
                'videos' => ['tech-fest-highlights.mp4'],
                'documents' => ['tech-fest-schedule.pdf'],
            ],
            [
                'type' => 'news',
                'title' => 'New Computer Lab Inaugurated with Latest Technology',
                'content' => '<p>MMP inaugurated a state-of-the-art computer laboratory equipped with 50 high-performance workstations, latest software, and networking infrastructure.</p><p>The lab features Intel Core i7 processors, 16GB RAM, dedicated graphics cards, and dual monitors for each workstation. Students will have access to industry-standard software including AutoCAD, SolidWorks, Adobe Creative Suite, and development tools.</p><p>The facility was inaugurated by Chief Guest Mr. Ram Prasad Sharma, Mayor of Budhiganga Rural Municipality.</p>',
                'images' => ['computer-lab-1.jpg', 'computer-lab-2.jpg', 'inauguration.jpg'],
                'videos' => ['lab-tour.mp4'],
                'documents' => [],
            ],
            [
                'type' => 'event',
                'title' => 'Industrial Visit to Hetauda Cement Factory',
                'content' => '<p>Civil Engineering students of 3rd semester will visit Hetauda Cement Factory for an educational industrial tour.</p><p><strong>Date:</strong> Jestha 10, 2081<br><strong>Departure:</strong> 6:00 AM from MMP Campus<br><strong>Return:</strong> 6:00 PM (approx)</p><p>Students will observe cement manufacturing processes, quality control procedures, and interact with industry professionals.</p><p>Interested students must register by Jestha 5, 2081.</p>',
                'images' => ['industrial-visit-poster.jpg'],
                'videos' => [],
                'documents' => ['visit-guidelines.pdf', 'registration-form.pdf'],
            ],
            [
                'type' => 'news',
                'title' => 'MMP Students Secure Top Positions in CTEVT Board Exams',
                'content' => '<p>Students of Manmohan Memorial Polytechnic have achieved remarkable success in the CTEVT Board Examinations 2080, securing top positions across multiple programs.</p><p><strong>Highlights:</strong></p><ul><li>Diploma in Civil Engineering: 1st Position - Sita Sharma (95.2%)</li><li>Diploma in Computer Engineering: 2nd Position - Rajesh Thapa (94.8%)</li><li>Diploma in Electrical Engineering: 3rd Position - Anita Rai (93.5%)</li></ul><p>Overall pass percentage: 98.5% (highest in Koshi Province)</p>',
                'images' => ['toppers-2080.jpg', 'result-celebration.jpg'],
                'videos' => [],
                'documents' => ['result-summary.pdf'],
            ],
            [
                'type' => 'event',
                'title' => 'Guest Lecture on AI and Machine Learning by Industry Expert',
                'content' => '<p>Department of Computer Engineering is organizing a guest lecture on "Artificial Intelligence and Machine Learning: Future of Technology"</p><p><strong>Speaker:</strong> Er. Prakash Shrestha<br>Senior AI Engineer, Fusemachines Nepal<br>10+ years experience in AI/ML</p><p><strong>Date:</strong> Jestha 8, 2081<br><strong>Time:</strong> 2:00 PM - 4:00 PM<br><strong>Venue:</strong> MMP Auditorium</p><p>Topics: Introduction to AI/ML, Career opportunities, Industry trends, Live demonstrations</p>',
                'images' => ['guest-lecture-poster.jpg'],
                'videos' => [],
                'documents' => [],
            ],
            [
                'type' => 'news',
                'title' => 'MMP Signs MoU with Leading Construction Companies',
                'content' => '<p>Manmohan Memorial Polytechnic has signed Memorandums of Understanding (MoU) with three leading construction companies for student internships and placement opportunities.</p><p><strong>Partner Companies:</strong></p><ul><li>Kalika Construction Pvt. Ltd.</li><li>Sagarmatha Engineering Works</li><li>Nepal Infrastructure Development Company</li></ul><p>The partnership will provide internship opportunities, industrial training, guest lectures, and guaranteed job interviews for graduating students.</p>',
                'images' => ['mou-signing.jpg', 'mou-ceremony.jpg'],
                'videos' => ['mou-highlights.mp4'],
                'documents' => ['mou-details.pdf'],
            ],
            [
                'type' => 'event',
                'title' => 'Sports Week 2081 - Inter-Department Championship',
                'content' => '<p>Get ready for Sports Week 2081! Inter-department sports championship featuring football, volleyball, basketball, badminton, and athletics.</p><p><strong>Date:</strong> Jestha 20-25, 2081<br><strong>Venue:</strong> MMP Sports Ground</p><p><strong>Events:</strong></p><ul><li>Football (Boys & Girls)</li><li>Volleyball (Boys & Girls)</li><li>Basketball (Boys & Girls)</li><li>Badminton (Singles & Doubles)</li><li>Athletics (100m, 200m, 400m, Relay)</li></ul><p>Registration deadline: Jestha 15, 2081</p>',
                'images' => ['sports-week-poster.jpg', 'sports-2080.jpg'],
                'videos' => [],
                'documents' => ['sports-schedule.pdf'],
            ],
        ];

        $this->command->info('Creating News & Events with attachments...');

        foreach ($newsEvents as $index => $item) {
            // Create the notice
            $notice = Notice::create([
                'title' => $item['title'],
                'slug' => Str::slug($item['title']),
                'content' => $item['content'],
                'type' => $item['type'],
                'created_by' => $admin->id,
                'is_published' => true,
                'published_at' => now()->subDays(rand(1, 30)),
            ]);

            // Create image attachments
            foreach ($item['images'] as $imageIndex => $imageName) {
                $filePath = "notices/sample-{$notice->id}-img-{$imageIndex}.jpg";
                $fullPath = storage_path('app/public/' . $filePath);
                
                // Create a placeholder image file (1x1 pixel)
                $this->createPlaceholderImage($fullPath);
                
                NoticeAttachment::create([
                    'notice_id' => $notice->id,
                    'file_path' => $filePath,
                    'file_name' => $imageName,
                    'file_type' => 'jpg',
                    'file_size' => filesize($fullPath),
                ]);
            }

            // Create video attachments
            foreach ($item['videos'] as $videoIndex => $videoName) {
                $filePath = "notices/sample-{$notice->id}-video-{$videoIndex}.mp4";
                $fullPath = storage_path('app/public/' . $filePath);
                
                // Create a placeholder video file
                $this->createPlaceholderVideo($fullPath);
                
                NoticeAttachment::create([
                    'notice_id' => $notice->id,
                    'file_path' => $filePath,
                    'file_name' => $videoName,
                    'file_type' => 'mp4',
                    'file_size' => filesize($fullPath),
                ]);
            }

            // Create document attachments
            foreach ($item['documents'] as $docIndex => $docName) {
                $filePath = "notices/sample-{$notice->id}-doc-{$docIndex}.pdf";
                $fullPath = storage_path('app/public/' . $filePath);
                
                // Create a placeholder PDF file
                $this->createPlaceholderPdf($fullPath);
                
                NoticeAttachment::create([
                    'notice_id' => $notice->id,
                    'file_path' => $filePath,
                    'file_name' => $docName,
                    'file_type' => 'pdf',
                    'file_size' => filesize($fullPath),
                ]);
            }

            $this->command->info("Created: {$item['title']}");
        }

        $this->command->info('News & Events seeded successfully!');
        $this->command->info('Total items created: ' . count($newsEvents));
    }

    /**
     * Create a placeholder image file (minimal JPEG)
     */
    private function createPlaceholderImage(string $path): void
    {
        $dir = dirname($path);
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        // Create a minimal valid JPEG file (1x1 pixel)
        // This is a base64 encoded 1x1 red pixel JPEG
        $jpegData = base64_decode('/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjL/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCwAA8A/9k=');
        
        file_put_contents($path, $jpegData);
    }

    /**
     * Create a placeholder video file (minimal MP4)
     */
    private function createPlaceholderVideo(string $path): void
    {
        $dir = dirname($path);
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        // Create a minimal placeholder file
        file_put_contents($path, 'Sample video placeholder - Replace with actual video file');
    }

    /**
     * Create a placeholder PDF file
     */
    private function createPlaceholderPdf(string $path): void
    {
        $dir = dirname($path);
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        // Create a minimal PDF structure
        $pdf = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n3 0 obj\n<< /Type /Page /Parent 2 0 R /Resources << /Font << /F1 << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> >> >> /MediaBox [0 0 612 792] /Contents 4 0 R >>\nendobj\n4 0 obj\n<< /Length 44 >>\nstream\nBT\n/F1 12 Tf\n100 700 Td\n(Sample PDF Document) Tj\nET\nendstream\nendobj\nxref\n0 5\n0000000000 65535 f\n0000000009 00000 n\n0000000058 00000 n\n0000000115 00000 n\n0000000317 00000 n\ntrailer\n<< /Size 5 /Root 1 0 R >>\nstartxref\n410\n%%EOF";
        
        file_put_contents($path, $pdf);
    }
}
