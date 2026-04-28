<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendWhatsAppMessage;
use App\Models\CustomerContact;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WhatsAppController extends Controller
{
    public function index(Request $r, WhatsAppService $wa)
    {
        $contacts = CustomerContact::query()
            ->search($r->input('q'))
            ->orderBy('username')
            ->paginate(20)
            ->withQueryString();

        return view('admin.whatsapp.index', [
            'status'   => $wa->status(),
            'contacts' => $contacts,
            'q'        => $r->input('q'),
        ]);
    }

    public function send(Request $r)
    {
        $data = $r->validate([
            'number'  => ['required', 'string', 'regex:/^[0-9+]{8,20}$/'],
            'message' => ['required', 'string', 'max:1500'],
        ]);
        $number = WhatsAppService::normalizePhone($data['number']);
        SendWhatsAppMessage::dispatch($number, $data['message'], null, auth()->id());
        return back()->with('ok', 'Pesan masuk antrean.');
    }

    public function storeContact(Request $r)
    {
        $data = $r->validate([
            'username' => ['required', 'string', 'max:64', 'unique:customer_contacts,username'],
            'phone'    => ['required', 'string', 'regex:/^[0-9+]{8,20}$/'],
            'name'     => ['nullable', 'string', 'max:100'],
            'notes'    => ['nullable', 'string', 'max:255'],
        ]);
        $data['phone'] = WhatsAppService::normalizePhone($data['phone']);
        CustomerContact::create($data);
        return back()->with('ok', "Kontak {$data['username']} ditambahkan.");
    }

    public function updateContact(Request $r, CustomerContact $contact)
    {
        $data = $r->validate([
            'phone' => ['required', 'string', 'regex:/^[0-9+]{8,20}$/'],
            'name'  => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);
        $data['phone'] = WhatsAppService::normalizePhone($data['phone']);
        $contact->update($data);
        return back()->with('ok', "Kontak {$contact->username} diupdate.");
    }

    public function destroyContact(CustomerContact $contact)
    {
        $u = $contact->username;
        $contact->delete();
        return back()->with('ok', "Kontak {$u} dihapus.");
    }
}
