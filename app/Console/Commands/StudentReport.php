<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class StudentReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:diagnostic-student-report {user_id} {report_type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'diagnostic report for a student';

    /**
     * Execute the console command. 
     */
    public function handle()
    {

        $this->info('Generating diagnostic report for student...');


         $drip->send(User::find($this->argument('user')));
    }
}
