<?php

namespace App\Livewire\Master\JamKerja;

use App\Enums\ShiftType;
use App\Models\Cabang;
use App\Models\JamKerja;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Master Jam Masuk - Soulmate Komputer')]
class Index extends Component
{
    public string $cabangFilter = '';

    public bool $modalOpen = false;

    public bool $isEditMode = false;

    public ?int $jamKerjaId = null;

    // Form fields
    public ?int $cabang_id = null;

    public string $nama_shift = 'pagi';

    public string $jam_rentang_mulai = '00:00';

    public string $jam_mulai = '07:00';

    public string $jam_masuk = '08:00';

    public int $toleransi_menit = 2;

    public int $batas_kritis_menit = 120;

    public string $jam_cutoff = '12:59';

    public bool $is_aktif = true;

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user || ! $user->isOwner()) {
            abort(403, 'Hanya Owner yang berhak mengelola Master Jam Masuk.');
        }
    }

    public function updatedNamaShift(string $val): void
    {
        if (! $this->isEditMode) {
            if ($val === 'pagi') {
                $this->jam_rentang_mulai = '00:00';
                $this->jam_mulai = '07:00';
                $this->jam_masuk = '08:00';
                $this->jam_cutoff = '12:59';
            } else {
                $this->jam_rentang_mulai = '13:00';
                $this->jam_mulai = '13:00';
                $this->jam_masuk = '14:00';
                $this->jam_cutoff = '23:59';
            }
        }
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset([
            'jamKerjaId',
            'cabang_id',
            'nama_shift',
            'jam_rentang_mulai',
            'jam_mulai',
            'jam_masuk',
            'toleransi_menit',
            'batas_kritis_menit',
            'jam_cutoff',
            'is_aktif',
        ]);
        $this->nama_shift = 'pagi';
        $this->jam_rentang_mulai = '00:00';
        $this->jam_mulai = '07:00';
        $this->jam_masuk = '08:00';
        $this->toleransi_menit = 2;
        $this->batas_kritis_menit = 120;
        $this->jam_cutoff = '12:59';
        $this->is_aktif = true;
        $this->isEditMode = false;
        $this->modalOpen = true;
    }

    public function openEditModal(int $id): void
    {
        $this->resetValidation();
        $rule = JamKerja::findOrFail($id);

        $this->jamKerjaId = $rule->id;
        $this->cabang_id = $rule->cabang_id;
        $this->nama_shift = $rule->nama_shift instanceof ShiftType ? $rule->nama_shift->value : (string) $rule->nama_shift;
        $this->jam_rentang_mulai = substr((string) ($rule->jam_rentang_mulai ?? '00:00'), 0, 5);
        $this->jam_mulai = substr((string) $rule->jam_mulai, 0, 5);
        $this->jam_masuk = substr((string) $rule->jam_masuk, 0, 5);
        $this->toleransi_menit = (int) $rule->toleransi_menit;
        $this->batas_kritis_menit = (int) ($rule->batas_kritis_menit ?? 120);
        $this->jam_cutoff = substr((string) $rule->jam_cutoff, 0, 5);
        $this->is_aktif = (bool) $rule->is_aktif;

        $this->isEditMode = true;
        $this->modalOpen = true;
    }

    public function closeModal(): void
    {
        $this->modalOpen = false;
        $this->resetValidation();
    }

    public function rules(): array
    {
        return [
            'cabang_id' => ['nullable', 'exists:cabangs,id'],
            'nama_shift' => ['required', 'in:pagi,sore'],
            'jam_rentang_mulai' => ['required', 'date_format:H:i'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_masuk' => ['required', 'date_format:H:i'],
            'toleransi_menit' => ['required', 'integer', 'min:0', 'max:120'],
            'batas_kritis_menit' => ['required', 'integer', 'min:10', 'max:720'],
            'jam_cutoff' => ['required', 'date_format:H:i'],
            'is_aktif' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_shift.required' => 'Shift kerja wajib dipilih.',
            'nama_shift.in' => 'Shift kerja tidak valid.',
            'jam_rentang_mulai.required' => 'Awal rentang jam shift wajib diisi.',
            'jam_rentang_mulai.date_format' => 'Format jam rentang mulai harus JJ:MM (contoh: 00:00).',
            'jam_mulai.required' => 'Jam batas awal wajar (buka toko) wajib diisi.',
            'jam_mulai.date_format' => 'Format jam mulai harus JJ:MM (contoh: 07:00).',
            'jam_masuk.required' => 'Jam target masuk kerja wajib diisi.',
            'jam_masuk.date_format' => 'Format jam masuk harus JJ:MM (contoh: 08:00).',
            'toleransi_menit.required' => 'Toleransi keterlambatan wajib diisi.',
            'toleransi_menit.integer' => 'Toleransi menit harus berupa angka.',
            'toleransi_menit.min' => 'Toleransi menit minimal 0 menit.',
            'toleransi_menit.max' => 'Toleransi menit maksimal 120 menit.',
            'batas_kritis_menit.required' => 'Batas terlambat kritis wajib diisi.',
            'batas_kritis_menit.integer' => 'Batas kritis harus berupa angka.',
            'jam_cutoff.required' => 'Batas akhir rentang shift wajib diisi.',
            'jam_cutoff.date_format' => 'Format jam cutoff harus JJ:MM (contoh: 12:59).',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $cabangVal = ! empty($this->cabang_id) ? (int) $this->cabang_id : null;

        // Validasi keunikan kombinasi cabang_id + nama_shift
        $duplicateQuery = JamKerja::where('nama_shift', $this->nama_shift);
        if ($cabangVal === null) {
            $duplicateQuery->whereNull('cabang_id');
        } else {
            $duplicateQuery->where('cabang_id', $cabangVal);
        }

        if ($this->isEditMode && $this->jamKerjaId) {
            $duplicateQuery->where('id', '!=', $this->jamKerjaId);
        }

        if ($duplicateQuery->exists()) {
            $lingkup = $cabangVal ? 'cabang terpilih' : 'default global';
            $this->addError('nama_shift', "Pengaturan shift {$this->nama_shift} untuk {$lingkup} sudah ada.");

            return;
        }

        $jamRentangMulaiFormatted = strlen($this->jam_rentang_mulai) === 5 ? $this->jam_rentang_mulai.':00' : $this->jam_rentang_mulai;
        $jamMulaiFormatted = strlen($this->jam_mulai) === 5 ? $this->jam_mulai.':00' : $this->jam_mulai;
        $jamMasukFormatted = strlen($this->jam_masuk) === 5 ? $this->jam_masuk.':00' : $this->jam_masuk;
        $jamCutoffFormatted = strlen($this->jam_cutoff) === 5 ? $this->jam_cutoff.':00' : $this->jam_cutoff;

        $data = [
            'cabang_id' => $cabangVal,
            'nama_shift' => $this->nama_shift,
            'jam_rentang_mulai' => $jamRentangMulaiFormatted,
            'jam_mulai' => $jamMulaiFormatted,
            'jam_masuk' => $jamMasukFormatted,
            'toleransi_menit' => (int) $this->toleransi_menit,
            'batas_kritis_menit' => (int) $this->batas_kritis_menit,
            'jam_cutoff' => $jamCutoffFormatted,
            'is_aktif' => (bool) $this->is_aktif,
            'updated_by' => Auth::id(),
        ];

        if ($this->isEditMode && $this->jamKerjaId) {
            $rule = JamKerja::findOrFail($this->jamKerjaId);
            $rule->update($data);
            session()->flash('success', "Pengaturan shift \"{$this->nama_shift}\" berhasil diperbarui.");
        } else {
            $data['created_by'] = Auth::id();
            JamKerja::create($data);
            session()->flash('success', "Pengaturan shift baru \"{$this->nama_shift}\" berhasil ditambahkan.");
        }

        $this->closeModal();
    }

    public function toggleStatus(int $id): void
    {
        $rule = JamKerja::findOrFail($id);
        $rule->update([
            'is_aktif' => ! $rule->is_aktif,
            'updated_by' => Auth::id(),
        ]);

        $statusText = $rule->is_aktif ? 'diaktifkan' : 'dinonaktifkan';
        session()->flash('success', "Aturan shift \"{$rule->nama_shift->value}\" berhasil {$statusText}.");
    }

    public function deleteJamKerja(int $id): void
    {
        $rule = JamKerja::findOrFail($id);

        if ($rule->cabang_id === null) {
            session()->flash('error', 'Aturan shift default global tidak boleh dihapus. Anda hanya dapat menonaktifkan atau mengeditnya.');

            return;
        }

        $shiftName = $rule->nama_shift->value;
        $cabangName = $rule->cabang?->nama_cabang ?? 'Cabang';
        $rule->delete();

        session()->flash('success', "Aturan khusus shift {$shiftName} untuk {$cabangName} berhasil dihapus (kembali ke default global).");
    }

    public function render()
    {
        $query = JamKerja::with(['cabang', 'updatedBy', 'createdBy']);

        if ($this->cabangFilter === 'global') {
            $query->whereNull('cabang_id');
        } elseif (! empty($this->cabangFilter)) {
            $query->where('cabang_id', (int) $this->cabangFilter);
        }

        $jamKerjas = $query->orderByRaw('cabang_id IS NOT NULL, cabang_id ASC')
            ->orderBy('nama_shift', 'asc')
            ->get();

        $cabangs = Cabang::orderBy('nama_cabang')->get();

        return view('livewire.master.jam-kerja.index', [
            'jamKerjas' => $jamKerjas,
            'cabangs' => $cabangs,
        ]);
    }
}
