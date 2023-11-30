<?php

namespace App\Services;

use App\Resume;
use App\ResumeAssessment;
use App\ResumeEducation;
use App\ResumeExperience;
use App\ResumeSkill;
use App\TalentPool;
use App\TalentPoolCategoryList;
use App\TalentPoolShare;
use App\TalentPoolShareCut;
use App\TalentPoolShareMark;
use App\User;
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
            $talentPools = TalentPool::query()
                ->with(["resume" => function ($q1) {
                    $q1
                        ->with('profileImage')
                        ->with("lastEducation")
                        ->with("lastAssessment")
                        ->with("skills")
                        ->with("summaries")
                        ->with(["lastExperience" => function ($q2) {
                            $q2->select(DB::raw('*, CASE
                        WHEN end_date IS NOT NULL THEN YEAR(end_date)
                        ELSE YEAR(start_date)
                        END AS year'));
                        }])
                        ->with(['recruitment' => function ($q2) {
                            $q2->select('owner_id', 'created_at');
                        }]);
                }])
                ->with('mark.requester.company')
                ->withCount('mark');
            if (!$keyword) {
                $talentPools = $talentPools->whereHas("resume", function ($q1) use ($skill, $role, $year, $education) {
                    if ($education)
                        $q1->whereHas("lastEducation", function ($q2) use ($education) {
                            if ($education) {
                                $q2->whereIn('university', $education);
                            }
                        });
                    if ($role)
                        $q1->whereHas("lastAssessment", function ($q2) use ($role) {
                            if ($role) {
                                $q2->whereIn('name', $role);
                            }
                        });
                    if ($skill)
                        $q1->whereHas("skills", function ($q2) use ($skill) {
                            if ($skill) {
                                $q2->whereIn('name', $skill);
                            }
                        });
                    if ($year) {
                        $q1->whereHas("lastExperience", function ($q2) use ($year) {
                            $q2->select(DB::raw('resume_id, CASE
                            WHEN end_date IS NOT NULL THEN YEAR(end_date)
                            ELSE YEAR(start_date)
                            END AS year'));
                            $q2->having('year', '=', $year);
                        });
                    };
                });
            } else {
                $talentPools = $talentPools->whereHas("resume", function ($q1) use ($skill, $role, $year, $education, $keyword) {
                    if ($skill || $role || $year || $education || $keyword) {
                        $q1->where(function ($q2) use ($skill, $role, $education, $keyword, $year) {
                            $q2->orWhereHas("lastEducation", function ($q3) use ($keyword, $education) {
                                if ($education) $q3->whereIn('university', $education);
                                if ($keyword) $q3->where('university', 'like', '%' . $keyword . '%');
                            })
                                ->orWhereHas("lastAssessment", function ($q3) use ($keyword, $role) {
                                    if ($role) $q3->whereIn('name', $role);
                                    if ($keyword) $q3->where('name', 'like', '%' . $keyword . '%');
                                })
                                ->orWhereHas("skills", function ($q3) use ($keyword, $skill) {
                                    if ($skill) $q3->whereIn('name', $skill);
                                    if ($keyword) $q3->where('name', 'like', '%' . $keyword . '%');
                                })
                                ->orWhereHas('lastExperience', function ($q3) use ($keyword, $year) {
                                    $q3->select(DB::raw('resume_id, CASE
                                WHEN end_date IS NOT NULL THEN YEAR(end_date)
                                ELSE YEAR(start_date)
                                END AS year'));
                                    if ($year) $q3->having('year', '=', $year);
                                    if ($keyword) $q3->having('year', 'like', '%' . $keyword . '%');
                                });
                        });
                        if ($keyword) {
                            $q1->orWhere('name', 'like', '%' . $keyword . '%');
                        }
                    }
                });
            }
            $talentPools = $talentPools->whereHas('category', function (Builder $q1) {
                $q1->where('company_id', auth()->user()->company_id);
            })->where('talent_pool_category_id', $request->category_id)->latest('mark_count')->latest('id');
            if ($status) $talentPools = $talentPools->whereIn('status', $status);
            $rows = $request->rows ?? 10;
            $talentPools = $talentPools->paginate($rows);
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $talentPools, "status" => 200];
        } catch (\Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    function getTalentPool($request, $route_name): array
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;

        $rules = [
            "id" => "numeric|required",
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        try {
            $talentPool = TalentPool::query()
                ->with(["resume" => function ($q1) {
                    $q1
                        ->with('profileImage')
                        ->with(["lastEducation" => function ($q2) {
                            $q2->select('resume_id', 'university');
                        }])
                        ->with(["lastAssessment" => function ($q2) {
                            $q2->select('id', 'name');
                        }])
                        ->with(["skills" => function ($q2) {
                        }])
                        ->with(["lastExperience" => function ($q2) {
                            $q2->select(DB::raw('role, resume_id, CASE
                            WHEN end_date IS NOT NULL THEN YEAR(end_date)
                            ELSE YEAR(start_date)
                            END AS year'));
                        }])
                        ->with(['recruitment' => function ($q2) {
                            $q2->select('owner_id', 'created_at');
                        }]);
                }])->find($request->id);
            if (!$talentPool) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $talentPool, "status" => 200];
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
                $check = TalentPool::query()
                    ->where('talent_pool_category_id', $request->category_id)
                    ->where('resume_id', $item)
                    ->first();
                if (!$check) {
                    $talent = new TalentPool;
                    $talent->talent_pool_category_id = $request->category_id;
                    $talent->resume_id = $item;
                    $talent->save();
                }
            }
            DB::commit();
            return ["success" => true, "message" => "Data Berhasil Ditambah", "status" => 200];
        } catch (Exception $err) {
            DB::rollBack();
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    function deleteTalentPool($request, $route_name): array
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;

        try {
            $id = $request->id;
            $talent = TalentPool::find($id);
            if (!$talent) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
            if (!$talent->delete()) return ["success" => false, "message" => "Gagal Menghapus Kategori", "status" => 400];
            return ["success" => true, "message" => "Data Berhasil Dihapus", "id" => $talent->id, "status" => 200];
        } catch (Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    //* Talent Pool Candidadates
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
                    $q1->whereHas('category', function (Builder $q2) {
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

    //* Talent Pool Categories
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
        $rules = [
            "id" => "required|numeric",
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
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

    //* Talent Pool Filters
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
                ->groupBy('resume_id')->groupBy('university')->get();

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

    //* Talent Pool Share
    function getTalentPoolShares($request, $route_name): array
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;
        $rules = [
            "category_id" => "required|numeric",
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        try {
            $shared = TalentPoolShare::query()->where('talent_pool_category_id', $request->category_id)->with(['user'])->latest('id')->get();
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $shared, "status" => 200];
        } catch (Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    function addTalentPoolShare($request, $route_name): array
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;
        $rules = [
            "category_id" => "required|numeric",
            "requester_id" => "required|numeric",
            "expired" => "numeric",
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        $checkUser = User::find($request->requester_id);
        if (!$checkUser) return ["success" => false, "message" => "Requester tidak ditemukan", "status" => 400];
        try {
            $generateLink = $this->generateLink(12, 3);
            $share = new TalentPoolShare();
            $share->talent_pool_category_id = $request->category_id;
            $share->code = $generateLink;
            $share->user_id = $request->requester_id;
            if ($request->expired) {
                $expired = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' + ' . $request->expired . ' days '));
                $share->expired = $expired;
            }
            $share->save();
            return ["success" => true, "message" => "Data Berhasil Ditambah", "data" => $share, "status" => 200];
        } catch (Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    function deleteTalentPoolShare($request, $route_name): array
    {
        $access = $this->globalService->checkRoute($route_name);
        if ($access["success"] === false) return $access;
        $rules = [
            "id" => "required|numeric"
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        try {
            $id = $request->id;
            $category = TalentPoolShare::find($id);
            if (!$category) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];
            if (!$category->delete()) return ["success" => false, "message" => "Gagal Menghapus Tautan", "status" => 400];
            return ["success" => true, "message" => "Data Berhasil Dihapus", "id" => $category->id, "status" => 200];
        } catch (Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    //* Talent Pool Share Public

    function authTalentPoolSharePublic($request, $route_name): array
    {
        $validator = Validator::make($request->all(), [
            "code" => "required"
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $code = $request->code;
        $shared = TalentPoolShare::query()->where('code', $code)->with(['user', 'category'])->first();
        if (!$shared) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];

        try {
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $shared, "status" => 200];
        } catch (Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    function getTalentPoolSharePublics($request, $route_name): array
    {
        $rules = [
            "share_id" => "required|numeric",
            "page" => "numeric",
            "rows" => "numeric|between:1,100",
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        $talentShare = TalentPoolShare::find($request->share_id);
        if (
            !$talentShare ||
            ($talentShare && $talentShare->expired != null
                && date('Y-m-d H:i:s') > $talentShare->expired)
        ) return ["success" => false, "message" => "Data tidak ditemukan, tautan mungkin sudah kadaluarsa atau dihapus", "status" => 404];
        $except = TalentPoolShareCut::query()->where('talent_pool_share_id', $talentShare->id)->pluck('talent_pool_id')->toArray();
        try {
            $skill = $request->skills ? explode(",", $request->skills) : NULL;
            $role = $request->roles ? explode(",", $request->roles) : NULL;
            $year = $request->years ? explode(",", $request->years) : NULL;
            $education = $request->educations ? explode(",", $request->educations) : NULL;
            $keyword = $request->keyword ?? NULL;
            $status = $request->status ? explode(",", $request->status) : NULL;
            $talentPools = TalentPool::query()
                ->with(["resume" => function ($q1) {
                    $q1
                        ->with('profileImage')
                        ->with("lastEducation")
                        ->with("lastAssessment")
                        ->with("skills")
                        ->with("summaries")
                        ->with(["lastExperience" => function ($q2) {
                            $q2->select(DB::raw('*, CASE
                            WHEN end_date IS NOT NULL THEN YEAR(end_date)
                            ELSE YEAR(start_date)
                            END AS year'));
                        }])
                        ->with(['recruitment' => function ($q2) {
                            $q2->select('owner_id', 'created_at');
                        }]);
                }])
                ->withCount(['mark' => function ($q) use ($request) {
                    $q->where('talent_pool_share_id', $request->share_id);
                }]);
            if (!$keyword) {
                $talentPools = $talentPools->whereHas("resume", function ($q1) use ($skill, $role, $year, $education) {
                    if ($education)
                        $q1->whereHas("lastEducation", function ($q2) use ($education) {
                            if ($education) {
                                $q2->whereIn('university', $education);
                            }
                        });
                    if ($role)
                        $q1->whereHas("lastAssessment", function ($q2) use ($role) {
                            if ($role) {
                                $q2->whereIn('name', $role);
                            }
                        });
                    if ($skill)
                        $q1->whereHas("skills", function ($q2) use ($skill) {
                            if ($skill) {
                                $q2->whereIn('name', $skill);
                            }
                        });
                    if ($year) {
                        $q1->whereHas("lastExperience", function ($q2) use ($year) {
                            $q2->select(DB::raw('*, CASE
                            WHEN end_date IS NOT NULL THEN YEAR(end_date)
                            ELSE YEAR(start_date)
                            END AS year'));
                            $q2->having('year', '=', $year);
                        });
                    };
                });
            } else {
                $talentPools = $talentPools->whereHas("resume", function ($q1) use ($skill, $role, $year, $education, $keyword) {
                    if ($skill || $role || $year || $education || $keyword) {
                        $q1->where(function ($q2) use ($skill, $role, $education, $keyword, $year) {
                            $q2->orWhereHas("lastEducation", function ($q3) use ($keyword, $education) {
                                if ($education) $q3->whereIn('university', $education);
                                if ($keyword) $q3->where('university', 'like', '%' . $keyword . '%');
                            })
                                ->orWhereHas("lastAssessment", function ($q3) use ($keyword, $role) {
                                    if ($role) $q3->whereIn('name', $role);
                                    if ($keyword) $q3->where('name', 'like', '%' . $keyword . '%');
                                })
                                ->orWhereHas("skills", function ($q3) use ($keyword, $skill) {
                                    if ($skill) $q3->whereIn('name', $skill);
                                    if ($keyword) $q3->where('name', 'like', '%' . $keyword . '%');
                                })
                                ->orWhereHas('lastExperience', function ($q3) use ($keyword, $year) {
                                    $q3->select(DB::raw('*, CASE
                                WHEN end_date IS NOT NULL THEN YEAR(end_date)
                                ELSE YEAR(start_date)
                                END AS year'));
                                    if ($year) $q3->having('year', '=', $year);
                                    if ($keyword) $q3->having('year', 'like', '%' . $keyword . '%');
                                });
                        });
                        if ($keyword) {
                            $q1->orWhere('name', 'like', '%' . $keyword . '%');
                        }
                    }
                });
            }
            $talentPools = $talentPools->where('talent_pool_category_id', $talentShare->talent_pool_category_id)->whereNotIn('id', $except)->latest('mark_count')->latest('id');
            if ($status) $talentPools = $talentPools->whereIn('status', $status);
            $rows = $request->rows ?? 10;
            $talentPools = $talentPools->paginate($rows);
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $talentPools, "status" => 200];
        } catch (Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    function getTalentPoolSharePublic($request, $route_name): array
    {
        $validator = Validator::make($request->all(), [
            "id" => "numeric|required"
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $id = $request->id;
        $resume = Resume::with(['educations', 'experiences', 'projects', 'skills', 'trainings', 'certificates', 'achievements', 'assessment', 'assessmentResults', 'summaries', 'profileImage'])->find($id);
        if (!$resume) return ["success" => false, "message" => "Data Tidak Ditemukan", "status" => 400];

        try {
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $resume, "status" => 200];
        } catch (Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    function markTalentPoolSharePublic($request, $route_name): array
    {
        $rules = [
            'share_id' => 'required|numeric',
            'talent_id' => 'required|numeric',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        $share = TalentPoolShare::find($request->share_id);
        if (!$share) return ["success" => false, "message" => "Tautan tidak ditemukan", "status" => 404];
        $talent = TalentPool::find($request->talent_id);
        if (!$talent) return ["success" => false, "message" => "Talent tidak ditemukan", "status" => 404];
        $check = TalentPoolShareMark::query()->where('talent_pool_share_id', $share->id)->where('talent_pool_id', $talent->id)->first();
        try {
            DB::beginTransaction();
            $message = '';
            if (!$check) {
                $mark = new TalentPoolShareMark;
                $mark->talent_pool_share_id = $share->id;
                $mark->talent_pool_id = $talent->id;
                $mark->user_id = $share->user_id;
                $mark->save();
                $message = 'Berhasil Ditandai';
            } else {
                $check->delete();
                $message = 'Batal Ditandai';
            }
            DB::commit();
            return ["success" => true, "message" => "Talent " . $message, "status" => 200];
        } catch (Exception $err) {
            DB::rollBack();
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    //* Talent Pool Share Public Cut
    function getTalentPoolSharePublicCuts($request, $route_name): array
    {
        $validator = Validator::make($request->all(), [
            "share_id" => "required|numeric"
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }

        $cut = TalentPoolShareCut::query()->where('talent_pool_share_id', $request->share_id)->with(['talent.resume' => function ($q) use ($request) {
            $q->with(["lastEducation"])
                ->with(["lastAssessment"])
                ->with('skills')
                ->with(['lastExperience']);
        }])->whereHas('talent.resume', function ($q) use ($request) {
            $q->when(isset($request->keyword), function ($q2) use ($request) {
                $q2->where('name', 'like',  '%' . $request->keyword . '%');
            });
        })->get();
        try {
            return ["success" => true, "message" => "Data Berhasil Diambil", "data" => $cut, "status" => 200];
        } catch (Exception $err) {
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    function addTalentPoolSharePublicCut($request, $route_name): array
    {
        $rules = [
            'share_id' => 'required|numeric',
            'talent_id' => 'required|numeric',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        $share = TalentPoolShare::find($request->share_id);
        if (!$share) return ["success" => false, "message" => "Tautan tidak ditemukan", "status" => 404];
        $talent = TalentPool::find($request->talent_id);
        if (!$talent) return ["success" => false, "message" => "Talent tidak ditemukan", "status" => 404];
        $check = TalentPoolShareCut::query()->where('talent_pool_share_id', $share->id)->where('talent_pool_id', $talent->id)->first();
        if ($check) return ["success" => false, "message" => "Talent sudah dieleminasi", "status" => 400];
        try {
            DB::beginTransaction();
            $cut = new TalentPoolShareCut;
            $cut->talent_pool_share_id = $share->id;
            $cut->talent_pool_id = $talent->id;
            $cut->save();
            DB::commit();
            return ["success" => true, "message" => "Talent Berhasil Dieliminasi", "status" => 200];
        } catch (Exception $err) {
            DB::rollBack();
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    function deleteTalentPoolSharePublicCut($request, $route_name): array
    {
        $rules = [
            'share_id' => 'required|numeric',
            'talent_id' => 'required|numeric',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        $share = TalentPoolShare::find($request->share_id);
        if (!$share) return ["success" => false, "message" => "Tautan tidak ditemukan", "status" => 404];
        $talent = TalentPool::find($request->talent_id);
        if (!$talent) return ["success" => false, "message" => "Talent tidak ditemukan", "status" => 404];
        $cut = TalentPoolShareCut::query()->where('talent_pool_share_id', $share->id)->where('talent_pool_id', $talent->id)->first();
        if (!$cut) return ["success" => false, "message" => "Data tidak ditemukan", "status" => 404];
        try {
            DB::beginTransaction();
            if (!$cut->delete()) return ["success" => false, "message" => "Gagal Membatalkan Eleminasi", "status" => 400];
            DB::commit();
            return ["success" => true, "message" => "Eliminasi Berhasil Dibatalkan", "data" => $cut, "status" => 200];
        } catch (Exception $err) {
            DB::rollBack();
            return ["success" => false, "message" => $err, "status" => 400];
        }
    }

    //* Talent Pool Share Public Filter
    function getTalentPoolSharePublicFilters($request, $route_name): array
    {
        $rules = [
            "share_id" => "numeric|required",
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ["success" => false, "message" => $errors, "status" => 400];
        }
        $talentShare = TalentPoolShare::find($request->share_id);
        try {
            $skill = ResumeSkill::query()
                ->select('name', 'id')
                ->whereHas('resume.talentPool.category', function (Builder $q) use ($talentShare) {
                    $q->where('id', $talentShare->talent_pool_category_id);
                })->latest('id')->groupBy('name')->get();

            $univ = ResumeEducation::query()
                ->select('university', 'id')
                ->whereHas('resume.talentPool.category', function (Builder $q) use ($talentShare) {
                    $q->where('id', $talentShare->talent_pool_category_id);
                })->latest('id')->orderBy('display_order', 'asc')
                ->groupBy('resume_id')->groupBy('university')->get();

            $status = TalentPool::query()->select('id', 'status')
                ->whereHas('category', function (Builder $q) use ($talentShare) {
                    $q->where('id', $talentShare->talent_pool_category_id);
                })
                ->groupBy('status')->orderBy('status')->get();

            $role = ResumeAssessment::query()
                ->select('name', 'id')
                ->whereHas('resumes.talentPool.category', function (Builder $q) use ($talentShare) {
                    $q->where('id', $talentShare->talent_pool_category_id);
                })
                ->orderBy('name')->get();

            $year = ResumeExperience::query()
                ->select(DB::raw('
                 CASE
                 WHEN end_date IS NOT NULL THEN YEAR(end_date)
                 ELSE YEAR(start_date)
                 END AS year
                 '))->whereHas('resume.talentPool.category', function (Builder $q) use ($talentShare) {
                    $q->where('id', $talentShare->talent_pool_category_id);
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

    private function generateLink($length, $dashInterval): String
    {
        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $link = '';
        $charLength = strlen($characters);

        for ($i = 0; $i < $length; $i++) {
            $link .= $characters[random_int(0, $charLength - 1)];
            if (($i + 1) % $dashInterval == 0 && $i != 0 && $i != $length - 1) {
                $link .= '-';
            }
        }

        return $link;
    }
}
