<?php

namespace App\Services;

use App\Resume;
use App\ResumeAssessment;
use App\ResumeEducation;
use App\ResumeExperience;
use App\ResumeSkill;
use App\TalentPool;
use App\TalentPoolCategoryList;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TalentPoolService
{
    protected $globalService;

    public function __construct()
    {
        $this->globalService = new GlobalService;
    }

    function getTalentPools($request, $route_name): array
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;
        $rules = [
            "category_id" => "numeric|required",
            "page" => "numeric",
            "rows" => "numeric|between:1,100",
            // "sort_by" => "in:name,role,email,telp",
            // "sort_type" => "in:asc,desc"
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try {
            $skill = $request->skills ? explode(",", $request->skills) : NULL;
            $role = $request->roles ? explode(",", $request->roles) : NULL;
            $year = $request->years ? explode(",", $request->years) : NULL;
            $education = $request->educations ? explode(",", $request->educations) : NULL;
            $keyword = $request->keyword ?? NULL;
            $status = $request->status ? explode(",", $request->status) : NULL;
            DB::enableQueryLog();
            $talentPools = TalentPool::query()
                ->with(["resume" => function ($q1) use ($skill, $role, $year, $education) {
                    $q1
                        ->with(["lastEducation" => function ($q2) use ($education) {
                            $q2->select('resume_educations.resume_id', 'resume_educations.university');
                            if ($education) {
                                $q2->whereIn('university', $education);
                            }
                        }])
                        ->with(["lastAssessment" => function ($q2) use ($role) {
                            $q2->select('resume_assessments.id', 'resume_assessments.name');
                            if ($role) {
                                $q2->whereIn('name', $role);
                            }
                        }])
                        ->with(["skills" => function ($q2) use ($skill) {
                            if ($skill) {
                                $q2->whereIn('name', $skill);
                            }
                        }]);
                    if ($year) {
                        $q1->whereHas('lastExperience', function (Builder $q2) use ($year) {
                            $q2->select(DB::raw('CASE
                            WHEN end_date IS NOT NULL THEN YEAR(end_date)
                            ELSE YEAR(start_date)
                            END AS year'));
                            $q2->having('year', '=', $year);
                        });
                    };
                }]);
            if (!$keyword) {
                $talentPools = $talentPools->whereHas("resume", function ($q1) use ($skill, $role, $year, $education) {
                    $q1
                        ->whereHas("lastEducation", function ($q2) use ($education) {
                            $q2->select('resume_educations.resume_id', 'resume_educations.university');
                            if ($education) {
                                $q2->whereIn('university', $education);
                            }
                        })
                        ->whereHas("lastAssessment", function ($q2) use ($role) {
                            $q2->select('resume_assessments.id', 'resume_assessments.name');
                            if ($role) {
                                $q2->whereIn('name', $role);
                            }
                        })
                        ->whereHas("skills", function ($q2) use ($skill) {
                            if ($skill) {
                                $q2->whereIn('name', $skill);
                            }
                        });
                    if ($year) {
                        $q1->whereHas('lastExperience', function (Builder $q2) use ($year) {
                            $q2->select(DB::raw('CASE
                            WHEN end_date IS NOT NULL THEN YEAR(end_date)
                            ELSE YEAR(start_date)
                            END AS year'));
                            $q2->having('year', '=', $year);
                        });
                    };
                });
            } else {
                $talentPools = $talentPools->whereHas("resume", function ($q1) use ($skill, $role, $year, $education, $keyword) {
                    $q1
                        ->orWhereHas("lastEducation", function ($q2) use ($keyword, $education) {
                            $q2->select('resume_educations.resume_id', 'resume_educations.university');
                            if ($education) $q2->whereIn('university', $education);
                            if ($keyword) $q2->where('university', 'like', '%' . $keyword . '%');
                        })
                        ->orWhereHas("lastAssessment", function ($q2) use ($keyword, $role) {
                            $q2->select('resume_assessments.id', 'resume_assessments.name');
                            if ($role) $q2->whereIn('name', $role);
                            if ($keyword) $q2->where('name', 'like', '%' . $keyword . '%');
                        })
                        ->orWhereHas("skills", function ($q2) use ($keyword, $skill) {
                            if ($skill) $q2->whereIn('name', $skill);
                            if ($keyword) $q2->where('name', 'like', '%' . $keyword . '%');
                        });
                    if ($keyword) $q1->orWhere('name', 'like', '%' . $keyword . '%');
                    if ($year) {
                        $q1->orWhereHas('lastExperience', function (Builder $q2) use ($year) {
                            $q2->select(DB::raw('CASE
                            WHEN end_date IS NOT NULL THEN YEAR(end_date)
                            ELSE YEAR(start_date)
                            END AS year'));
                            $q2->having('year', '=', $year);
                        });
                    };
                });
            }
            $talentPools = $talentPools->whereHas('category', function (Builder $q1) {
                $q1->where('company_id', auth()->user()->company_id);
            })
                ->where('talent_pool_category_id', $request->category_id);
            if ($status) $talentPools = $talentPools->whereIn('status', $status);
            $rows = $request->rows ?? 10;
            $talentPools = $talentPools->paginate($rows);
            // dd(DB::getQueryLog());
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $talentPools, "status" => 200];
        } catch (\Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    function addTalentPool($request, $route_name): array
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;
        $rules = [
            'resume_ids' => 'required|array',
            'resume_ids.*' => 'numeric',
            'category_id' => 'required|numeric'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        try {
            DB::beginTransaction();
            foreach ($request->resume_ids as $item) {
                $talent = new TalentPool;
                $talent->talent_pool_category_id = $request->category_id;
                $talent->resume_id = $item;
                $talent->save();
            }
            DB::commit();
            return ["success" => true, "message" => "Data Berhasil Ditambah", "status" => 200];
        } catch (Exception $err) {
            DB::rollBack();
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    function getTalentPoolCandidates($request, $route_name): array
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;
        $rules = [
            "category_id" => "numeric|required",
            "page" => "numeric",
            "rows" => "numeric|between:1,100",
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        try {
            $keyword = $request->keyword;
            $cadidates = Resume::query()
                ->whereDoesntHave('talentPool', function (Builder $q1) use ($request) {
                    $q1->where('talent_pool_category_id', $request->category_id);
                    $q1->whereDoesntHave('category', function (Builder $q2) {
                        $q2->where('company_id', auth()->user()->company_id);
                    });
                })
                ->with(["lastEducation" => function ($q) {
                    $q->select('resume_educations.resume_id', 'resume_educations.university');
                }])
                ->with(["lastAssessment" => function ($q) {
                    $q->select('resume_assessments.id', 'resume_assessments.name');
                }])
                ->with('skills')
                ->with(['lastExperience' => function ($q) {
                    $q->select('resume_experiences.id', 'resume_experiences.role');
                }]);

            if ($keyword) {
                $cadidates = $cadidates->where('name', 'like',  '%' . $keyword . '%');
            }

            $rows = $request->rows ?? 5;

            $cadidates = $cadidates->paginate($rows);
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $cadidates, "status" => 200];
        } catch (Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    function getTalentPoolCategories($request, $route_name): array
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;
        try {
            $talentPoolCategories = TalentPoolCategoryList::query()
                ->where('company_id', auth()->user()->company_id)->latest('id')->get();
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $talentPoolCategories, "status" => 200];
        } catch (Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    function addTalentPoolCategory($request, $route_name): array
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;
        $rules = [
            "name" => "required",
            "description" => "required",
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        try {
            $category = new TalentPoolCategoryList;
            $category->name = $request->name;
            $category->description = $request->description;
            $category->company_id = auth()->user()->company_id;
            $category->save();
            return ["success" => true, "message" => "Data Berhasil Ditambah", "data" => $category, "status" => 200];
        } catch (Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    function deleteTalentPoolCategory($request, $route_name): array
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;

        try {
            $id = $request->id;
            $category = TalentPoolCategoryList::find($id);
            if (!$category) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
            if (!$category->delete()) return ["success" => false, "message" => "Gagal Menghapus Kategori", "status" => 400];
            return ["success" => true, "message" => "Data Berhasil Dihapus", "id" => $category->id, "status" => 200];
        } catch (Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    function getTalentPoolFilters($request, $route_name): array
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;
        $rules = [
            "category_id" => "numeric|required",
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        try {
            $skill = ResumeSkill::query()
                ->select('name', 'id')
                ->whereHas('resume.talentPool.category', function (Builder $q) use ($request) {
                    $q->where('id', $request->category_id)
                        ->where('company_id', auth()->user()->company_id);
                })->latest('id')->groupBy('name')->get();

            $univ = ResumeEducation::query()
                ->select('university', 'id')
                ->whereHas('resume.talentPool.category', function (Builder $q) use ($request) {
                    $q->where('id', $request->category_id)
                        ->where('company_id', auth()->user()->company_id);
                })->latest('id')->orderBy('display_order', 'asc')
                ->groupBy('resume_id')->get();

            $status = TalentPool::query()->select('id', 'status')
                ->whereHas('category', function (Builder $q) use ($request) {
                    $q->where('id', $request->category_id)
                        ->where('company_id', auth()->user()->company_id);
                })
                ->groupBy('status')->orderBy('status')->get();

            $role = ResumeAssessment::query()
                ->select('name', 'id')
                ->whereHas('resumes.talentPool.category', function (Builder $q) use ($request) {
                    $q->where('id', $request->category_id)
                        ->where('company_id', auth()->user()->company_id);
                })
                ->orderBy('name')->get();

            $year = ResumeExperience::query()
                ->select(DB::raw('
                CASE
                WHEN end_date IS NOT NULL THEN YEAR(end_date)
                ELSE YEAR(start_date)
                END AS year
                '))->whereHas('resume.talentPool.category', function (Builder $q) use ($request) {
                    $q->where('id', $request->category_id)
                        ->where('company_id', auth()->user()->company_id);
                })->groupBy('year')->orderBy('year')->get();

            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => [
                "role" => $role,
                "skill" => $skill,
                "year" => $year,
                "university" => $univ,
                "status" => $status,
            ], "status" => 200];
        } catch (Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }
}
