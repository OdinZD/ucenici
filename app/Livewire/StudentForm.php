<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\KataTeam;
use App\Models\Location;
use App\Models\Student;
use App\Models\TrainingGroup;
use App\Rules\Oib;
use App\Sports\Grade;
use App\Sports\Sport;
use App\Support\AgeCategory;
use App\Support\MedicalStatus;
use Carbon\CarbonImmutable;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

final class StudentForm extends Component
{
    public bool $open = false;

    public ?int $studentId = null;

    /** Errors are shown only after the first save attempt (SPEC §4.6). */
    public bool $submitted = false;

    // Form fields — all strings so empty inputs round-trip cleanly; cast on save.
    public string $lastName = '';

    public string $firstName = '';

    public string $birthDate = '';

    public string $oib = '';

    public string $locationId = '';

    public string $trainingGroupId = '';

    public string $belt = '';

    public string $nextGradeStatus = 'none';

    public string $nextGradeCode = '';

    public string $medicalValidUntil = '';

    public bool $hasNoMedical = false;

    public string $parentName = '';

    public string $parentPhone = '';

    public string $parentEmail = '';

    public string $note = '';

    public bool $flagT = false;

    /** Kata team ids the student belongs to (many-to-many; kept as strings for checkbox binding). */
    public array $kataTeamIds = [];

    protected function rules(): array
    {
        return [
            'lastName' => ['required', 'string', 'max:100'],
            'firstName' => ['required', 'string', 'max:100'],
            'birthDate' => ['nullable', 'date', 'after_or_equal:1900-01-01', 'before_or_equal:today'],
            'oib' => ['nullable', new Oib, Rule::unique('students', 'oib')->ignore($this->studentId)],
            'locationId' => ['nullable', 'exists:locations,id'],
            'trainingGroupId' => ['nullable', 'exists:training_groups,id'],
            'belt' => ['nullable', Rule::in($this->gradeCodes())],
            'nextGradeStatus' => [Rule::in(['none', 'to_register', 'registered'])],
            'nextGradeCode' => [
                Rule::requiredIf($this->nextGradeStatus === 'registered'),
                'nullable',
                Rule::in($this->registerableGradeCodes()),
            ],
            'medicalValidUntil' => ['nullable', 'date', Rule::prohibitedIf($this->hasNoMedical)],
            'hasNoMedical' => ['boolean'],
            'parentName' => ['nullable', 'string', 'max:150'],
            'parentPhone' => ['nullable', 'string', 'max:30'],
            'parentEmail' => ['nullable', 'email'],
            'note' => ['nullable', 'string', 'max:2000'],
            'flagT' => ['boolean'],
            'kataTeamIds' => ['array'],
            'kataTeamIds.*' => ['exists:kata_teams,id'],
        ];
    }

    protected function messages(): array
    {
        return [
            'lastName.required' => 'Obavezno polje',
            'firstName.required' => 'Obavezno polje',
            'birthDate.before_or_equal' => 'Datum rođenja ne može biti u budućnosti',
            'birthDate.after_or_equal' => 'Provjeri datum rođenja',
            'oib.unique' => 'Polaznik s ovim OIB-om već postoji',
            'parentEmail.email' => 'Provjeri e-mail adresu',
            'nextGradeCode.required' => 'Odaberi stupanj',
        ];
    }

    #[On('new-student')]
    public function openCreate(?string $locationSlug = null): void
    {
        $this->resetForm();
        $this->studentId = null;

        $slug = $locationSlug !== null && $locationSlug !== '' ? $locationSlug : 'os-stanovi';
        $this->locationId = (string) (Location::where('slug', $slug)->value('id') ?? '');

        $this->open = true;
    }

    #[On('edit-student')]
    public function openEdit(int $id): void
    {
        $this->resetForm();

        $student = Student::findOrFail($id);
        $this->studentId = $student->id;
        $this->lastName = $student->last_name;
        $this->firstName = $student->first_name;
        $this->birthDate = $student->birth_date?->format('Y-m-d') ?? '';
        $this->oib = $student->oib ?? '';
        $this->locationId = (string) ($student->location_id ?? '');
        $this->trainingGroupId = (string) ($student->training_group_id ?? '');
        $this->belt = $student->belt_code ?? '';
        $this->nextGradeStatus = $student->next_grade_status->value;
        $this->nextGradeCode = $student->next_grade_code ?? '';
        $this->medicalValidUntil = $student->medical_valid_until?->format('Y-m-d') ?? '';
        $this->hasNoMedical = $student->has_no_medical;
        $this->parentName = $student->parent_name ?? '';
        $this->parentPhone = $student->parent_phone ?? '';
        $this->parentEmail = $student->parent_email ?? '';
        $this->note = $student->note ?? '';
        $this->flagT = $student->flag_t;
        $this->kataTeamIds = $student->kataTeams->pluck('id')->sort()->values()->map(fn (int $id): string => (string) $id)->all();

        $this->open = true;
    }

    public function close(): void
    {
        $this->open = false;
    }

    #[On('student-deleted')]
    public function closeAfterDelete(): void
    {
        $this->open = false;
    }

    public function updated(string $name): void
    {
        if ($name === 'hasNoMedical' && $this->hasNoMedical) {
            $this->medicalValidUntil = '';
        }

        if ($this->submitted) {
            $this->validateOnly($name);
        }
    }

    /** Segmented "Upis sljedećeg stupnja" control with the SPEC §4.8 suggestion. */
    public function selectNextGrade(string $status): void
    {
        $this->nextGradeStatus = $status;

        if ($status === 'registered' && $this->nextGradeCode === '') {
            $this->nextGradeCode = Sport::active()->suggestNextGradeCode($this->belt ?: null) ?? '';
        }

        if ($this->submitted) {
            $this->validateOnly('nextGradeCode');
        }
    }

    public function requestDelete(): void
    {
        if ($this->studentId !== null) {
            $this->dispatch('confirm-delete-student', id: $this->studentId);
        }
    }

    public function save(): void
    {
        $this->submitted = true;
        $this->validate();

        $data = [
            'last_name' => $this->lastName,
            'first_name' => $this->firstName,
            'birth_date' => $this->birthDate ?: null,
            'oib' => $this->oib ?: null,
            'location_id' => $this->locationId ?: null,
            'training_group_id' => $this->trainingGroupId ?: null,
            'belt_code' => $this->belt ?: null,
            'next_grade_status' => $this->nextGradeStatus,
            'next_grade_code' => $this->nextGradeStatus === 'registered' ? ($this->nextGradeCode ?: null) : null,
            'medical_valid_until' => $this->hasNoMedical ? null : ($this->medicalValidUntil ?: null),
            'has_no_medical' => $this->hasNoMedical,
            'parent_name' => $this->parentName ?: null,
            'parent_phone' => $this->parentPhone !== '' ? trim($this->parentPhone) : null,
            'parent_email' => $this->parentEmail ?: null,
            'note' => $this->note ?: null,
            'flag_t' => $this->flagT,
        ];

        $student = $this->studentId !== null
            ? tap(Student::findOrFail($this->studentId))->update($data)
            : Student::create($data);

        if (Sport::hasFeature('kata_teams')) {
            $student->kataTeams()->sync($this->kataTeamIds);
        }

        $this->dispatch('students-changed');
        $this->dispatch('toast', message: "Spremljeno: {$student->last_name} {$student->first_name}");
        $this->close();
    }

    private function resetForm(): void
    {
        $this->reset([
            'lastName', 'firstName', 'birthDate', 'oib', 'locationId', 'trainingGroupId',
            'belt', 'nextGradeStatus', 'nextGradeCode', 'medicalValidUntil', 'hasNoMedical',
            'parentName', 'parentPhone', 'parentEmail', 'note', 'flagT', 'kataTeamIds', 'submitted',
        ]);
    }

    public function render()
    {
        $today = CarbonImmutable::now();
        $birth = rescue(fn () => $this->birthDate !== '' ? CarbonImmutable::parse($this->birthDate) : null, null, false);
        $validUntil = rescue(fn () => $this->medicalValidUntil !== '' ? CarbonImmutable::parse($this->medicalValidUntil) : null, null, false);

        return view('livewire.student-form', [
            'ageCategory' => $birth ? AgeCategory::for($birth, $today) : null,
            'medicalStatus' => MedicalStatus::for($validUntil, $this->hasNoMedical, $today),
            'locations' => Location::orderBy('name')->get(),
            'groups' => TrainingGroup::orderBy('name')->get(),
            'belts' => Sport::active()->grades(),
            'kataTeams' => Sport::hasFeature('kata_teams') ? KataTeam::orderBy('name')->get() : collect(),
        ]);
    }

    /** @return list<string> all belt codes (incl. "none") for the belt field. */
    private function gradeCodes(): array
    {
        return array_map(fn (Grade $grade): string => $grade->code, Sport::active()->grades());
    }

    /** @return list<string> registerable belt codes (excl. "none") for the next-grade field. */
    private function registerableGradeCodes(): array
    {
        return array_values(array_map(
            fn (Grade $grade): string => $grade->code,
            array_filter(Sport::active()->grades(), fn (Grade $grade): bool => ! $grade->isNone),
        ));
    }
}
