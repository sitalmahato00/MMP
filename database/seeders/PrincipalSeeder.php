<?php

namespace Database\Seeders;

use App\Models\Executive;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class PrincipalSeeder extends Seeder
{
    public function run(): void
    {
        // Create storage directory for executives
        $principalAvatar = 'executives/principal-avatar.jpg';
        $principalAvatarPath = storage_path('app/public/' . $principalAvatar);
        
        if (!file_exists(dirname($principalAvatarPath))) {
            File::makeDirectory(dirname($principalAvatarPath), 0755, true);
        }
        
        // Create a placeholder principal photo (1x1 pixel JPEG)
        $principalPhotoData = base64_decode('/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjL/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCwAA8A/9k=');
        file_put_contents($principalAvatarPath, $principalPhotoData);
        
        // Create or update principal
        Executive::updateOrCreate(
            ['type' => 'principal', 'is_current' => true],
            [
                'name' => 'Er. Binay Mahato',
                'type' => 'principal',
                'designation' => 'Principal',
                'start_date_bs' => '2078-01-01',
                'end_date_bs' => null,
                'is_current' => true,
                'avatar' => $principalAvatar,
                'message' => 'Welcome to Manmohan Memorial Polytechnic. We are committed to providing quality technical education and producing skilled professionals who can contribute to the development of our nation. Our institution focuses on practical skills, industry partnerships, and holistic development of students.',
                'order' => 1,
            ]
        );

        $this->command->info('Principal created successfully!');
    }
}
