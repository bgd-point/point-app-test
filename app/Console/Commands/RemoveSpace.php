<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Point\Framework\Models\Formulir;
use Symfony\Component\Process\Process;

class RemoveSpace extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:trim {table} {field}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'remove whitespace in field of table';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->comment('process started');

        $table = $this->argument('table');
        $field = $this->argument('field');
        $list_data = \DB::table($table)->get();
        foreach ($list_data as $data) {
            if ($data->$field) {
                $fixed = trim($data->$field);

                \DB::table($table)
                    ->where('id', $data->id)
                    ->update([$field => $fixed]);
            }
        }

        $this->comment('process finished');
    }
}