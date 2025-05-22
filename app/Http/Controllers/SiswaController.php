<?php

namespace App\Http\Controllers;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SiswaController extends Controller
{
    public function index()

    {
        $siswa = Siswa::with('kelas')->paginate(5); // Tambahkan paginate(10)
        $kelas = Kelas::all();
        return view('siswa.index', compact('siswa', 'kelas'));
    }


    public function store(Request $request)
{
    $validated = $request->validate([
        'nama' => 'required|string|max:255',
        'nis' => 'required|string|unique:siswa,nis|max:20',
        'jenis_kelamin' => 'nullable|in:L,P',
        'alamat' => 'nullable|string',
    ]); 

    $guru = Auth::user()->guru;


    if (!$guru) {
        abort(403, 'Akses hanya untuk guru.');
    }

    // Ambil satu kelas pertama yang diajar guru ini
    $kelas = Kelas::where('guru_id', $guru->id)->first();

    if (!$kelas) {
        return back()->withErrors(['kelas_id' => 'Anda belum memiliki kelas.']);
    }
     $user =  User::create([
                'name' => $request->nama,
                'password' => hash::make('test123'),
                'nip' => $request->nis, 
                'email' => $request->nama.'@gmail.com',
                'role' => 'siswa',        
            ]);
            
            // Tambahkan kelas_id ke data validasi
            $validated['user_id'] = $user->id;
            $validated['kelas_id'] = $kelas->id;

    // Simpan siswa
    $siswa = Siswa::create($validated);  

    return redirect()->route('siswa.index')->with('success', 'Data siswa berhasil ditambahkan');
}

        

    public function update(Request $request, Siswa $siswa)
    {
        $validated = $request->validate([
            'nama' => 'required',
            'nis' => 'required|unique:siswa,nis,'.$siswa->id,
            'kelas_id' => 'required|exists:kelas,id',
            'jenis_kelamin' => 'nullable|in:L,P',
            'alamat' => 'nullable'
        ]);

        
        $siswa->update($validated);

        return redirect()->route('siswa.index')
            ->with('success', 'Data siswa berhasil diperbarui');
    }

    public function destroy(Siswa $siswa)
    {
        $siswa->delete();

        return redirect()->route('siswa.index')
            ->with('success', 'Data siswa berhasil dihapus');
    }


    /**
     * Import data siswa dari file CSV
     */
// SiswaController.php

public function importCSV(Request $request)
{
    $request->validate([
        'file' => 'required|file|mimes:csv,txt',
        'kelas_id' => 'required|exists:kelas,id',
    ]);

    $file = $request->file('file');
    $path = $file->getRealPath();

    if (($handle = fopen($path, 'r')) === false) {
        return redirect()->route('siswa.index')->with('error', 'Gagal membuka file CSV');
    }

    $header = fgetcsv($handle);
    if (!$header) {
        return redirect()->route('siswa.index')->with('error', 'File CSV kosong atau format tidak valid');
    }

    $header = array_map('strtolower', $header);
    $requiredColumns = ['nama', 'nis'];

    foreach ($requiredColumns as $col) {
        if (!in_array($col, $header)) {
            fclose($handle);
            return redirect()->route('siswa.index')->with('error', "Kolom '$col' tidak ditemukan di CSV");
        }
    }

    $columnIndexes = [];
    foreach ($header as $idx => $col) {
        $columnIndexes[$col] = $idx;
    }

    $imported = 0;
    $failed = 0;
    $errors = [];
    $lineNumber = 1;

    while (($row = fgetcsv($handle)) !== false) {
        $lineNumber++;

        if (empty($row[$columnIndexes['nama']]) || empty($row[$columnIndexes['nis']])) {
            $failed++;
            $errors[] = "Baris $lineNumber: Data wajib (nama, nis) tidak lengkap";
            continue;
        }

        try {
            $user = User::firstOrCreate(
                ['nip' => $row[$columnIndexes['nis']]],
                [
                    'name' => $row[$columnIndexes['nama']],
                    'email' => null,
                    'password' => bcrypt('password123'),
                    'role' => 'siswa',
                ]
            );

            if ($user->name !== $row[$columnIndexes['nama']]) {
                $user->name = $row[$columnIndexes['nama']];
                $user->save();
            }

            $dataSiswa = [
                'user_id' => $user->id,
                'kelas_id' => $request->kelas_id,
                'nama' => $row[$columnIndexes['nama']],
                'nis' => $row[$columnIndexes['nis']],
                'alamat' => $columnIndexes['alamat'] ?? null ? $row[$columnIndexes['alamat']] : null,
                'jenis_kelamin' => $columnIndexes['jenis_kelamin'] ?? null ? strtoupper($row[$columnIndexes['jenis_kelamin']]) : null,
            ];

            if (isset($dataSiswa['jenis_kelamin']) && !in_array($dataSiswa['jenis_kelamin'], ['L', 'P'])) {
                $dataSiswa['jenis_kelamin'] = null;
            }

            $siswa = Siswa::where('nis', $dataSiswa['nis'])->first();
            if ($siswa) {
                $siswa->update($dataSiswa);
            } else {
                Siswa::create($dataSiswa);
            }

            $imported++;
        } catch (\Exception $e) {
            $failed++;
            $errors[] = "Baris $lineNumber: " . $e->getMessage();
        }
    }

    fclose($handle);

    $message = "Berhasil import $imported data siswa.";
    if ($failed > 0) {
        $message .= " Gagal import $failed data.";
        return redirect()->route('siswa.index')->with('warning', $message)->with('importErrors', $errors);
    }

    return redirect()->route('siswa.index')->with('success', $message);
}

public function exportCSVTemplate()
{
    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="template_siswa.csv"',
    ];

    $callback = function () {
        $file = fopen('php://output', 'w');
        fputcsv($file, ['nama', 'nis', 'jenis_kelamin', 'alamat']);
        fputcsv($file, ['Budi Santoso', '12345678', 'L', 'Jl. Contoh 123']);
        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

}