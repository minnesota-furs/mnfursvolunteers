<?php

namespace App\Console\Commands;

use App\Models\Page;
use Illuminate\Console\Command;

class GenerateHomePage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'page:generate-home';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a default home page with slug "home"';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Check if home page already exists
        if (Page::where('slug', 'home')->exists()) {
            $this->error('A page with slug "home" already exists!');
            
            if ($this->confirm('Do you want to recreate it?', false)) {
                Page::where('slug', 'home')->delete();
            } else {
                return Command::FAILURE;
            }
        }

        // Create default home page with basic GrapesJS structure
        $defaultGjsData = [
            'assets' => [],
            'styles' => [],
            'pages' => [
                [
                    'id' => 'home',
                    'frames' => [
                        [
                            'component' => [
                                'type' => 'wrapper',
                                'stylable' => true,
                                'components' => [
                                    [
                                        'tagName' => 'section',
                                        'attributes' => ['id' => 'home-hero', 'class' => 'hero-section'],
                                        'components' => [
                                            [
                                                'tagName' => 'div',
                                                'attributes' => ['class' => 'container'],
                                                'components' => [
                                                    [
                                                        'tagName' => 'h1',
                                                        'type' => 'text',
                                                        'content' => 'Welcome to ' . config('app.name'),
                                                    ],
                                                    [
                                                        'tagName' => 'p',
                                                        'type' => 'text',
                                                        'content' => 'This is your home page. Edit this page using the page builder.',
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            'styles' => '.hero-section { min-height: 400px; background-color: #f3f4f6; display: flex; align-items: center; justify-content: center; text-align: center; padding: 2rem; } .container h1 { font-size: 3rem; font-weight: bold; margin-bottom: 1rem; } .container p { font-size: 1.25rem; color: #6b7280; }'
                        ]
                    ]
                ]
            ]
        ];

        $page = Page::create([
            'slug' => 'home',
            'gjs_data' => json_encode($defaultGjsData)
        ]);

        $this->info('Home page created successfully!');
        $this->line('Slug: home');
        $this->line('You can now visit / to see the home page');
        $this->line('Edit the page at: /grapesjs/' . $page->id);

        return Command::SUCCESS;
    }
}
