<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Restricts visibility of medical records based on the authenticated user.
 *
 * - No authenticated user (CLI / queue / seeder): no filter applied.
 * - Admin role: no filter applied (full visibility).
 * - Doctor / associate doctor: only records they own (filtered by $doctorColumn).
 * - Any other role (secretary, nurse, researcher): records belonging to clinics
 *   the user is assigned to. For models that have clinic_id directly, that column
 *   is filtered. For Patient (which is many-to-many with clinics), the
 *   clinic_patient pivot is used via whereExists.
 *
 * This is the security boundary for record visibility — controllers must not
 * rely on session('active_clinic_id') alone.
 */
class MedicalRecordScope implements Scope
{
    public function __construct(
        public bool $useClinicPivot = false,
        public string $doctorColumn = 'doctor_id',
    ) {}

    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::user();

        if (!$user) {
            return;
        }

        if ($user->role === 'admin') {
            return;
        }

        $table = $model->getTable();

        if ($user->isDoctor()) {
            $builder->where("{$table}.{$this->doctorColumn}", $user->id);
            return;
        }

        $clinicIds = $user->clinics()->pluck('clinics.id')->all();

        if (empty($clinicIds)) {
            $builder->whereRaw('1 = 0');
            return;
        }

        if ($this->useClinicPivot) {
            $builder->whereExists(function ($query) use ($clinicIds, $table) {
                $query->select(DB::raw(1))
                    ->from('clinic_patient')
                    ->whereColumn('clinic_patient.patient_id', "{$table}.id")
                    ->whereIn('clinic_patient.clinic_id', $clinicIds);
            });
            return;
        }

        $builder->whereIn("{$table}.clinic_id", $clinicIds);
    }
}
