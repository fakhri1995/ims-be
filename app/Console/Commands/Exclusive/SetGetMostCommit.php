<?php

namespace App\Console\Commands\Exclusive;

use App\AttendanceUser;
use App\ResumeEducation;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SetGetMostCommit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exclusive:set-get-most-commit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $owner = "migthy-mig";
        $repo = "ims-fe";
        $branch = "main";
        $per_page = "100";
        // $max_iterations = 6; // untuk repo ims
        $max_iterations = 13; // untuk repo ims-fe
        $result = "";
        $result_commits = [];
        $lastCommitDate = [];
        $result = [];

        $client = new Client();

        for ($run = 1; $run <= $max_iterations; $run++) {
            $url = "https://api.github.com/repos/{$owner}/{$repo}/commits?sha={$branch}&per_page={$per_page}&page={$run}";
            $response = $client->get($url, [
                'headers' => [
                    'Accept' => 'application/vnd.github+json',
                    'Authorization' => 'Bearer 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',
                    'X-GitHub-Api-Version' => '2022-11-28',
                ],
            ]);

            $commits = json_decode($response->getBody(), true);

            if (count($commits)) {
                array_push($lastCommitDate, end($commits)['commit']['author']['date']);
                foreach ($commits as $commit) {
                    array_push($result_commits, $commit['commit']['author']['name']);
                }
            } else {
                break;
            }
        }

        // dd(date('Y-m-d H:i', strtotime(end($lastCommitDate))));

        foreach ($result_commits as $name) {
            if (!isset($result[$name])) {
                $result[$name] = 1;
            } else {
                $result[$name]++;
            }
        }

        $formattedResult = [];

        foreach ($result as $name => $total) {
            $formattedResult[] = ['name' => $name, 'total' => $total];
        }

        usort($formattedResult, function ($a, $b) {
            return $b['total'] - $a['total'];
        });

        dump('Get Most Commit on Repo ' . $repo);
        dump('Period ( ' . date('Y-m-d H:i') . ' s/d ' . date('Y-m-d H:i', strtotime(end($lastCommitDate))) . ' )');

        $final_result = json_decode(json_encode($formattedResult));

        foreach ($final_result as $i => $item) {
            dump($i+1 . ' ' . $item->name . ' dengan total commits => ' . $item->total);
        }
    }
}
