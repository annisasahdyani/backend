<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Berita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\BeritaResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreBeritaRequest;
use App\Http\Requests\UpdateBeritaRequest;
class BeritaController extends Controller
{

     public function login(Request $request)
    {
        // Validasi data login
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false, // Menandakan gagal
                'message' => 'Invalid credentials'
            ]);
        }

        // Buat token
        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token
        ]);
    }
    public function register(Request $request)
    {
        // Validasi input
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        // Buat user baru
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);

        // Buat token untuk user baru
        $token = $user->createToken('auth_token')->plainTextToken;

        // Kirim respons berhasil
        return response()->json([
            'success' => true,
            'message' => 'User registered successfully.',
            'token' => $token,
        ]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $berita = Berita::paginate(9);
        $beritaa = $berita = Berita::orderBy('tanggal', 'desc')->paginate(9);

        return BeritaResource::collection($beritaa);
    }
    public function beritaAwal()
    {
        // Ambil 4 berita terbaru, diurutkan berdasarkan tanggal dalam urutan menurun
        $berita = Berita::orderBy('tanggal', 'desc')->take(4)->get();

        return BeritaResource::collection($berita);
    }
    public function getImage(Request $request)
    {
        $path = storage_path('app/public/gambar-berita/' . $request->input('gambar'));

        if (!File::exists($path)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        $file = File::get($path);
        $type = File::mimeType($path);

        return response($file, 200)->header("Content-Type", $type);
        // try {
        //     // Cari berita berdasarkan ID
        //     $berita = Berita::findOrFail($id);

        //     // Ambil nama file gambar dari atribut 'gambar'
        //     $filename = $berita->gambar;
        //     $path = storage_path('app/public/gambar-berita/' . $filename);

        //     // Cek apakah file gambar ada
        //     if (!File::exists($path)) {
        //         return response()->json(['error' => 'File not found'], 404);
        //     }

        //     // Ambil file gambar dan MIME type
        //     $file = File::get($path);
        //     $type = File::mimeType($path);

        //     // Kembalikan file sebagai response dengan MIME type yang sesuai
        //     return response($file, 200)->header("Content-Type", $type);
        // } catch (\Exception $e) {
        //     return response()->json([
        //         'message' => 'Gagal mengambil gambar: ' . $e->getMessage()
        //     ], 500);
        // }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBeritaRequest $request)
    {
        try {
            $filename = null;
            if ($request->hasFile('gambar')) {
                $foto = $request->file('gambar');
                $filename = date('Y-m-d') . '-' . $foto->getClientOriginalName();
                $path = 'gambar-berita/' . $filename;
                Storage::disk('public')->put($path, file_get_contents($foto));
            }

            // Mengganti input 'gambar' dengan filename di request
            $data = $request->all();
            $data['gambar'] = $filename; // Set 'gambar' menjadi nama file

            $berita = Berita::create($data);

            return response()->json([
                'success' => true, // Menambahkan properti 'success'
                'message' => 'Data Berhasil ditambahkan',
                'data' => $berita // Kembalikan data berita yang baru ditambahkan
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, // Menambahkan properti 'success'
                'message' => "Terjadi Kesalahan: " . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // try {
        //     // Mencari berita berdasarkan ID
        //     $berita = Berita::findOrFail($id);

        //     // Mengembalikan resource dalam format json
        //     return new BeritaResource($berita);
        // } catch (\Exception $e) {
        //     return response()->json([
        //         'message' => 'Berita tidak ditemukan: ' . $e->getMessage()
        //     ], 404);
        // }


        try {
            // Mencari berita berdasarkan ID
            $berita = Berita::findOrFail($id);

            // Mengembalikan resource dalam format json, termasuk URL gambar
            return response()->json([
                'id' => $berita->id,
                'judul_berita' => $berita->judul_berita,
                'gambar' => $berita->gambar,
                'isi_berita' => $berita->isi_berita,
                'tanggal' => $berita->tanggal,
                'created_at' => $berita->created_at,
                'updated_at' => $berita->updated_at
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Berita tidak ditemukan: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Berita $berita) {}

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBeritaRequest $request, $id)
    {
        try {
            // Cari berita berdasarkan ID
            $berita = Berita::findOrFail($id);
    
            // Cek jika ada file gambar baru yang di-upload
            if ($request->hasFile('gambar')) {
                // Upload gambar baru
                $foto = $request->file('gambar');
                $filename = date('Y-m-d') . '-' . $foto->getClientOriginalName();
                $path = 'gambar-berita/' . $filename;
                Storage::disk('public')->put($path, file_get_contents($foto));
    
                // Hapus gambar lama jika ada
                if ($berita->gambar && Storage::disk('public')->exists('gambar-berita/' . $berita->gambar)) {
                    Storage::disk('public')->delete('gambar-berita/' . $berita->gambar);
                }
    
                // Update gambar di database
                $berita->gambar = $filename;
            }
    
            // Update data berita lainnya
            $berita->judul_berita = $request->input('judul_berita');
            $berita->isi_berita = $request->input('isi_berita');
            $berita->tanggal = $request->input('tanggal');
    
            // Simpan perubahan
            $berita->save();
    
            return response()->json([
                'message' => 'Data Berhasil diupdate'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => "Terjadi Kesalahan: " . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Berita $berita)
    {
        //
    }
}
