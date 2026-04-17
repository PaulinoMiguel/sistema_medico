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
 * - Doctor: only records they own. Two modes:
 *     useDoctorPivot = true: Patient — via doctor_patient pivot table.
 *     default: via $doctorColumn on the model's table.
 * - Any other role (secretary, nurse, ...): records belonging to clinics
 *   the user is assigned to. Three modes:
 *     useClinicPivot = true: Patient — via clinic_patient pivot.
 *     secretaryViaDoctorClinic = true: Service — via doctor's clinic.
 *     default: via clinic_id column on the model's table.
 */
class MedicalRecordScope implements Scope
{
    public function __construct(
        public bool $useClinicPivot = false,
        public string $doctorColumn = 'doctor_id',
        public bool $secretaryViaDoctorClinic = false,
        public bool $useDoctorPivot = false,
    ) {}

    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::user();

        if (!$user) {
            return;
        }

        $table = $model->getTable();

        if ($user->isDoctor()) {
            if ($this->useDoctorPivot) {
                $builder->whereExists(function ($query) use ($user, $table) {
                    $query->select(DB::raw(1))
                        ->from('doctor_patient')
                        ->whereColumn('doctor_patient.patient_id', "{$table}.id")
                        ->where('doctor_patient.doctor_id', $user->id);
                });
            } else {
                $builder->where("{$table}.{$this->doctorColumn}", $user->id);
            }
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
