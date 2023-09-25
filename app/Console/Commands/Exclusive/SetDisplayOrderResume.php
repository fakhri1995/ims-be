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
    protected $signature = 'exclusive:set-display-order-resume';

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
        dump('memulai proses');
        $resumes = Resume::query()->orderBy('id', 'DESC')->get();

        dump(count($resumes) . ' data akan di proses');
        foreach ($resumes as $list) {
            try {
                DB::beginTransaction();

                // check udah pernah di set belum experiencenya, kalau belum harusnya display order ada yang null
                $checkExperience = ResumeExperience::query()->where('resume_id', $list->id)->whereNull('display_order')->first();
                if ($checkExperience) {
                    $experiences = ResumeExperience::query()->where('resume_id', $list->id)->orderBy('id', 'DESC')->get();
                    foreach ($experiences as $i => $item) {
                        $item->display_order = $i + 1;
                        $item->save();
                    }
                }

                // check udah pernah di set belum projectnya, kalau belum harusnya display order ada yang null
                $checkProject = ResumeProject::query()->where('resume_id', $list->id)->whereNull('display_order')->first();
                if ($checkProject) {
                    $projects = ResumeProject::query()->where('resume_id', $list->id)->orderBy('id', 'DESC')->get();
                    foreach ($projects as $i => $item) {
                        $item->display_order = $i + 1;
                        $item->save();
                    }
                }

                // check udah pernah di set belum achievementnya, kalau belum harusnya display order ada yang null
                $checkAchievement = ResumeAchievement::query()->where('resume_id', $list->id)->whereNull('display_order')->first();
                if ($checkAchievement) {
                    $achievements = ResumeAchievement::query()->where('resume_id', $list->id)->orderBy('id', 'DESC')->get();
                    foreach ($achievements as $i => $item) {
                        $item->display_order = $i + 1;
                        $item->save();
                    }
                }

                // check udah pernah di set belum educationnya, kalau belum harusnya display order ada yang null
                $checkEducation = ResumeEducation::query()->where('resume_id', $list->id)->whereNull('display_order')->first();
                if ($checkEducation) {
                    $educations = ResumeEducation::query()->where('resume_id', $list->id)->orderBy('id', 'DESC')->get();
                    foreach ($educations as $i => $item) {
                        $item->display_order = $i + 1;
                        $item->save();
                    }
                }

                // check udah pernah di set belum certificatenya, kalau belum harusnya display order ada yang null
                $checkCertificate = ResumeCertificate::query()->where('resume_id', $list->id)->whereNull('display_order')->first();
                if ($checkCertificate) {
                    $certificates = ResumeCertificate::query()->where('resume_id', $list->id)->orderBy('id', 'DESC')->get();
                    foreach ($certificates as $i => $item) {
                        $item->display_order = $i + 1;
                        $item->save();
                    }
                }

                // check udah pernah di set belum trainingnya, kalau belum harusnya display order ada yang null
                $checkTraining = ResumeTraining::query()->where('resume_id', $list->id)->whereNull('display_order')->first();
                if ($checkTraining) {
                    $trainings = ResumeTraining::query()->where('resume_id', $list->id)->orderBy('id', 'DESC')->get();
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
            } catch (\Throwable $th) {
                DB::rollBack();
                dump('error di resume id => ' . $list->id);
            }
        }
    }
}
