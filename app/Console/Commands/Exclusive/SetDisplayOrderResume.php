<?php

namespace App\Console\Commands\Exclusive;

use App\Resume;
use App\ResumeAchievement;
use App\ResumeCertificate;
use App\ResumeEducation;
use App\ResumeExperience;
use App\ResumeProject;
use App\ResumeTraining;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SetDisplayOrderResume extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exclusive:set-display-order-resume {page}';

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
        $page = (int) $this->argument('page') ?? 1;
        $rows = 200;

        dump('memulai proses');
        $resumes = Resume::query()->orderBy('id', 'DESC')
            ->where(function ($query) {
                $query
                    ->whereHas('experiences', function ($q) {
                        $q
                            ->groupBy('resume_id')
                            ->havingRaw('SUM(CASE WHEN display_order <> 1 THEN 0 ELSE 1 END) = 0');
                    })
                    ->orWhereHas('projects', function ($q) {
                        $q
                            ->groupBy('resume_id')
                            ->havingRaw('SUM(CASE WHEN display_order <> 1 THEN 0 ELSE 1 END) = 0');
                    })
                    ->orWhereHas('achievements', function ($q) {
                        $q
                            ->groupBy('resume_id')
                            ->havingRaw('SUM(CASE WHEN display_order <> 1 THEN 0 ELSE 1 END) = 0');
                    })
                    ->orWhereHas('educations', function ($q) {
                        $q
                            ->groupBy('resume_id')
                            ->havingRaw('SUM(CASE WHEN display_order <> 1 THEN 0 ELSE 1 END) = 0');
                    })
                    ->orWhereHas('certificates', function ($q) {
                        $q
                            ->groupBy('resume_id')
                            ->havingRaw('SUM(CASE WHEN display_order <> 1 THEN 0 ELSE 1 END) = 0');
                    })
                    ->orWhereHas('trainings', function ($q) {
                        $q
                            ->groupBy('resume_id')
                            ->havingRaw('SUM(CASE WHEN display_order <> 1 THEN 0 ELSE 1 END) = 0');
                    });
            })
            ->simplePaginate($rows, ['*'], 'page', $page);

        dump(count($resumes) . ' data akan di proses');
        foreach ($resumes as $list) {
            try {
                DB::beginTransaction();

                // check udah pernah di set belum experiencenya, kalau belum harusnya display order ada yang null
                // $checkExperience = ResumeExperience::query()->where('resume_id', $list->id)->whereNull('display_order')->first();
                $checkExperience = ResumeExperience::query()->where('resume_id', $list->id)->first();
                $updatedIfNull = ResumeExperience::whereNull('display_order')->update(['display_order' => '99']);
                if ($checkExperience) {
                    $experiences = ResumeExperience::query()->where('resume_id', $list->id)->orderBy('display_order', 'ASC')->orderBy('id', 'DESC')->get();
                    foreach ($experiences as $i => $item) {
                        $item->display_order = $i + 1;
                        $item->save();
                    }
                }

                // check udah pernah di set belum projectnya, kalau belum harusnya display order ada yang null
                // $checkProject = ResumeProject::query()->where('resume_id', $list->id)->whereNull('display_order')->first();
                $checkProject = ResumeProject::query()->where('resume_id', $list->id)->first();
                $updatedIfNull = ResumeProject::whereNull('display_order')->update(['display_order' => '99']);
                if ($checkProject) {
                    $projects = ResumeProject::query()->where('resume_id', $list->id)->orderBy('display_order', 'ASC')->orderBy('id', 'DESC')->get();
                    foreach ($projects as $i => $item) {
                        $item->display_order = $i + 1;
                        $item->save();
                    }
                }

                // check udah pernah di set belum achievementnya, kalau belum harusnya display order ada yang null
                // $checkAchievement = ResumeAchievement::query()->where('resume_id', $list->id)->whereNull('display_order')->first();
                $checkAchievement = ResumeAchievement::query()->where('resume_id', $list->id)->first();
                $updatedIfNull = ResumeAchievement::whereNull('display_order')->update(['display_order' => '99']);
                if ($checkAchievement) {
                    $achievements = ResumeAchievement::query()->where('resume_id', $list->id)->orderBy('display_order', 'ASC')->orderBy('id', 'DESC')->get();
                    foreach ($achievements as $i => $item) {
                        $item->display_order = $i + 1;
                        $item->save();
                    }
                }

                // check udah pernah di set belum educationnya, kalau belum harusnya display order ada yang null
                // $checkEducation = ResumeEducation::query()->where('resume_id', $list->id)->whereNull('display_order')->first();
                $checkEducation = ResumeEducation::query()->where('resume_id', $list->id)->first();
                $updatedIfNull = ResumeEducation::whereNull('display_order')->update(['display_order' => '99']);
                if ($checkEducation) {
                    $educations = ResumeEducation::query()->where('resume_id', $list->id)->orderBy('display_order', 'ASC')->orderBy('id', 'DESC')->get();
                    foreach ($educations as $i => $item) {
                        $item->display_order = $i + 1;
                        $item->save();
                    }
                }

                // check udah pernah di set belum certificatenya, kalau belum harusnya display order ada yang null
                // $checkCertificate = ResumeCertificate::query()->where('resume_id', $list->id)->whereNull('display_order')->first();
                $checkCertificate = ResumeCertificate::query()->where('resume_id', $list->id)->first();
                $updatedIfNull = ResumeCertificate::whereNull('display_order')->update(['display_order' => '99']);
                if ($checkCertificate) {
                    $certificates = ResumeCertificate::query()->where('resume_id', $list->id)->orderBy('display_order', 'ASC')->orderBy('id', 'DESC')->get();
                    foreach ($certificates as $i => $item) {
                        $item->display_order = $i + 1;
                        $item->save();
                    }
                }

                // check udah pernah di set belum trainingnya, kalau belum harusnya display order ada yang null
                // $checkTraining = ResumeTraining::query()->where('resume_id', $list->id)->whereNull('display_order')->first();
                $checkTraining = ResumeTraining::query()->where('resume_id', $list->id)->first();
                $updatedIfNull = ResumeTraining::whereNull('display_order')->update(['display_order' => '99']);
                if ($checkTraining) {
                    $trainings = ResumeTraining::query()->where('resume_id', $list->id)->orderBy('display_order', 'ASC')->orderBy('id', 'DESC')->get();
                    foreach ($trainings as $i => $item) {
                        $item->display_order = $i + 1;
                        $item->save();
                    }
                }

                DB::commit();
                if ($checkExperience || $checkProject || $checkAchievement || $checkEducation || $checkCertificate || $checkTraining) {
                    dump('berhasil set resume id => ' . $list->id);
                } else {
                    dump('resume id => ' . $list->id . ' sudah di set sebelumnya atau tidak ada yang harus di set');
                }
            } catch (\Error $err) {
                DB::rollBack();
                dump('error di resume id => ' . $list->id);
                dump('errornya :');
                dump($err->getMessage());
                dump('====================');
            }
        }
    }
}
