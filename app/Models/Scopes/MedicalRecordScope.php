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
 * - Doctor (doctor_admin / doctor_associate): only records they own
 *   (filtered by $doctorColumn).
 * - Any other role (secretary, nurse, ...): records belonging to clinics
 *   the user is assigned to. Three modes for the secretary path:
 *
 *     useClinicPivot = true:
 *         Patient case — visibility through the clinic_patient pivot table
 *         (a patient may belong to multiple clinics).
 *
 *     secretaryViaDoctorClinic = true:
 *         Service case — services have no clinic_id, only doctor_id. The
 *         secretary sees a service if its doctor works at one of her clinics.
 *
 *     default:
 *         Appointment / Consultation / Payment — model has clinic_id directly,
 *         filter is whereIn('clinic_id', $clinicIds).
 *
 * Note: super admins live in the separate `admins` table (different guard),
 * so they never reach this scope through the User model.
 *
 * This is the security boundary for record visibility — controllers must not
 * rely on session('active_clinic_id') alone.
 */
class MedicalRecordScope implements Scope
{
    public function __construct(
        public bool $useClinicPivot = false,
        public string $doctorColumn = 'doctor_id',
        public bool $secretaryViaDoctorClinic = false,
    ) {}

    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::user();

        if (!$user) {
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

        if ($this->secretaryViaDoctorClinic) {
            $doctorCol = $this->doctorColumn;
            $builder->whereExists(function ($query) use ($clinicIds, $table, $doctorCol) {
                $query->select(DB::raw(1))
                    ->from('clinic_user')
                    ->whereColumn('clinic_user.user_id', "{$table}.{$doctorCol}")
                    ->whereIn('clinic_user.clinic_id', $clinicIds);
            });
            return;
        }

        $builder->whereIn("{$table}.clinic_id", $clinicIds);
    }
}
