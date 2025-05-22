<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use Illuminate\Http\Request;
use App\Models\User;
use App\Exports\GuruExport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Excel as ExcelType;


class GuruController extends Controller
{
    public function index()
    {
        $guru = Guru::all();
        return view('guru.index', compact('guru'));
    }


// TAMBAH GURU

    public function store(Request $request)
    {
        
            $validated = $request->validate([
                'nama' => 'required',
                'nip' => 'required|unique:guru,nip',
                // validasi kolom lainnya
            ]);

        $user =  User::create([
                'name' => $request->nama,
                'password' => hash::make('test123'),
                'nip' => $request->nip, 
                'email' => $request->nama.'@gmail.com',
                'role' => 'guru',        
            ]);

            $validated['user_id'] = $user->id;

            $guru = Guru::create($validated);    
            
            return redirect()->route('guru.index')->with('success', 'Data guru berhasil ditambahkan');
    }

public function update(Request $request, Guru $guru)
{
    $validated = $request->validate([
        'nama' => 'required',
        'nip' => 'required|unique:guru,nip,' . $guru->id,
    ]);

    $oldNip = $guru->nip;  // simpan nip lama

    $guru->update($validated);

    if ($oldNip !== $validated['nip']) {
        $user = User::where('nip', $oldNip)->first();
        if ($user) {
            $user->nip = $validated['nip'];
            $user->save();
        }
    }

    return redirect()->route('guru.index')->with('success', 'Data guru berhasil diperbarui');
}



public function destroy(Guru $guru)
{
    // Cari user yang punya nip sama dengan nip guru yang akan dihapus
    $user = User::where('nip', $guru->nip)->first();

    if ($user) {
        $user->delete(); // hapus data user dulu
    }

    $guru->delete(); // baru hapus data guru

    return redirect()->route('guru.index')->with('success', 'Data guru dan user terkait berhasil dihapus');
}


    
public function export()
{
    if (!in_array(Auth::user()->role, ['admin', 'kepsek'])) {
        abort(403); // Hanya admin dan kepsek yang boleh export
    }

    $data = \App\Models\Guru::select('id', 'nama', 'nip')->get();

    $exportData = new class($data) implements FromCollection {
        protected $data;

        public function __construct($data)
        {
            $this->data = $data;
        }

        public function collection()
        {
            return collect([
                ['ID', 'Nama', 'NIP'],
                ...$this->data->map(fn($d) => [$d->id, $d->nama, $d->nip])->toArray()
            ]);
        }
    };

    return Excel::download(new GuruExport, 'data-guru.xlsx');

}
     


}
