<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Hash;
use App\Helpers\ImageHelper;

class CustomerController extends Controller
{

    public function index()
    {
        $customer = Customer::orderBy('id', 'desc')->get();
        return view('backend.v_customer.index', [
            'judul' => 'Customer',
            'sub' => 'Halaman Customer',
            'index' => $customer
        ]);
    }

    // Redirect ke Google 
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    // Callback dari Google 
    public function callback()
    {
        try {
            $socialUser = Socialite::driver('google')->user();

            // Cek apakah email sudah terdaftar 
            $registeredUser = User::where('email', $socialUser->email)->first();

            // dd($socialUser);

            if (!$registeredUser) {
                // Buat user baru 
                $user = User::create([
                    'nama' => $socialUser->name,
                    'email' => $socialUser->email,
                    'password' => Hash::make('default_password'), // Password default 
                    'hp' => '0000000000', // Nomor HP default

                ]);

                // Buat data customer 
                Customer::create([
                    'user_id' => $user->id,
                    'google_id' => $socialUser->id,
                    'google_token' => $socialUser->token
                ]);

                // Login pengguna baru 
                Auth::login($user);
            } else {
                // Jika email sudah terdaftar, langsung login 
                Auth::login($registeredUser);
            }

            // Redirect ke halaman utama 
            return redirect()->intended('beranda');
        } catch (\Exception $e) {
            // dd($e->getMessage()); 
            // Redirect ke halaman utama jika terjadi kesalahan 
            return redirect('/')->with('error', 'Terjadi kesalahan saat login dengan 
Google.');
        }
    }

    public function edit(string $id)
    {
        //
        $customer = Customer::findOrFail($id);
        $user = User::findOrFail($customer->user_id);
        // dd($user);
        return view('backend.v_customer.edit', [
            'judul' => 'Ubah Customer',
            'customer' => $customer,
            'user' => $user
        ]);
    }

    public function update(Request $request, string $id)
    {
        //
        //ddd($request);
        $user = User::findOrFail($id);
        $rules = [
            'nama' => 'required|max:255',
            'role' => 'required',
            'status' => 'required',
            'hp' => 'required|min:10|max:13',
            'foto' => 'image|mimes:jpeg,jpg,png,gif|file|max:1024',
        ];
        if ($request->email != $user->email) {
            $rules['email'] = 'required|max:255|email|unique:user';
        }
        $validatedData = $request->validate($rules);
        // menggunakan ImageHelper

        if ($request->file('foto')) {
            //hapus gambar lama
            if ($user->foto) {
                $oldImagePath = public_path('storage/img-user/') . $user->foto;
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            $file = $request->file('foto');
            $extension = $file->getClientOriginalExtension();
            $originalFileName = date('YmdHis') . '_' . uniqid() . '.' . $extension;
            $directory = 'storage/img-customer/';
            // Simpan gambar dengan ukuran yang ditentukan
            ImageHelper::uploadAndResize($file, $directory, $originalFileName, 385, 400);
            // null (jika tinggi otomatis)
            // Simpan nama file asli di database
            $validatedData['foto'] = $originalFileName;
        }

        $user->update($validatedData);
        return redirect()->route('backend.customer.index')->with('success', 'Data berhasil diperbaharui');
    }

    public function destroy(string $id)
    {
        //
        $user = user::findOrFail($id);
        // dd($user);
        $customer = Customer::where('user_id', $id)->first();
        $user->delete();
        return redirect()->route('backend.v_customer')->with('success', 'Data berhasil dihapus');
    }

    public function logout(Request $request)
    {
        Auth::logout(); // Logout pengguna 
        $request->session()->invalidate(); // Hapus session 
        $request->session()->regenerateToken(); // Regenerate token CSRF 

        return redirect('/')->with('success', 'Anda telah berhasil logout.');
    }

    public function akun($id)
    {
        $loggedInCustomerId = Auth::user()->id;

        if ($id != $loggedInCustomerId) {
            return redirect()->route('customer.akun', ['id' => $loggedInCustomerId])
                ->with('msgError', 'Anda tidak berhak mengakses akun ini.');
        }

        $customer = Customer::with('user')->where('user_id', $id)->firstOrFail();

        // dd($customer->user);

        return view('v_customer.edit', [
            'judul' => 'Customer',
            'subJudul' => 'Akun Customer',
            'edit' => $customer
        ]);
    }


    public function updateAkun(Request $request, $id)
    {
        $customer = Customer::where('user_id', $id)->firstOrFail();
        $rules = [
            'nama' => 'required|max:255',
            'hp' => 'required|min:10|max:13',
            'foto' => 'image|mimes:jpeg,jpg,png,gif|file|max:1024',
        ];
        $messages = [
            'foto.image' => 'Format gambar gunakan file dengan ekstensi jpeg, jpg, png, atau gif.',
            'foto.max' => 'Ukuran file gambar Maksimal adalah 1024 KB.'
        ];

        if ($request->email != $customer->user->email) {
            $rules['email'] = 'required|max:255|email|unique:customer';
        }
        if ($request->alamat != $customer->alamat) {
            $rules['alamat'] = 'required';
        }
        if ($request->pos != $customer->pos) {
            $rules['pos'] = 'required';
        }

        $validatedData = $request->validate($rules, $messages);
        // menggunakan ImageHelper 
        if ($request->file('foto')) {
            //hapus gambar lama 
            if ($customer->user->foto) {
                $oldImagePath = public_path('storage/img-customer/') . $customer->user->foto;
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            $file = $request->file('foto');
            $extension = $file->getClientOriginalExtension();
            $originalFileName = date('YmdHis') . '_' . uniqid() . '.' . $extension;
            $directory = 'storage/img-customer/';
            // Simpan gambar dengan ukuran yang ditentukan 
            ImageHelper::uploadAndResize($file, $directory, $originalFileName, 385, 400); // null (jika tinggi otomatis) 
            // Simpan nama file asli di database 
            $validatedData['foto'] = $originalFileName;
        }

        $customer->user->update($validatedData);

        $customer->update([
            'alamat' => $request->input('alamat'),
            'pos' => $request->input('pos'),
        ]);
        return redirect()->route('customer.akun', $id)->with('success', 'Data berhasil diperbarui');
    }
}
