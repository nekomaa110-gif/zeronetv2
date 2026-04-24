<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MikrotikService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RouterController extends Controller
{
    public function __construct(private MikrotikService $svc) {}

    // ─── Pages ────────────────────────────────────────────────────────────

    public function index()
    {
        $routers = collect($this->svc->routers())
            ->map(fn ($cfg, $id) => ['id' => $id, ...$cfg]);

        return view('admin.routers.index', compact('routers'));
    }

    public function show(string $router)
    {
        $cfg = $this->svc->routerConfig($router);

        return view('admin.routers.show', [
            'routerId'   => $router,
            'routerName' => $cfg['name'],
            'routerHost' => $cfg['host'],
        ]);
    }

    // ─── AJAX endpoints ───────────────────────────────────────────────────

    public function stats(string $router)
    {
        try {
            return response()->json([
                'online' => true,
                'stats'  => $this->svc->stats($router),
            ]);
        } catch (Exception $e) {
            return response()->json(['online' => false, 'error' => $e->getMessage()]);
        }
    }

    public function traffic(string $router)
    {
        try {
            return response()->json([
                'online' => true,
                ...$this->svc->trafficInterface($router),
            ]);
        } catch (Exception $e) {
            return response()->json(['online' => false, 'error' => $e->getMessage()]);
        }
    }

    public function hotspotUsers(string $router)
    {
        try {
            return response()->json([
                'success' => true,
                'users'   => $this->svc->hotspotUsers($router),
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // ─── Actions ──────────────────────────────────────────────────────────

    public function disconnect(Request $request, string $router)
    {
        $request->validate(['session_id' => 'required|string']);

        try {
            $this->svc->disconnectUser($router, $request->session_id);
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function reboot(string $router)
    {
        try {
            $cfg = $this->svc->routerConfig($router);
            $this->svc->reboot($router);
            return back()->with('success', "Router {$cfg['name']} sedang reboot...");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal reboot: ' . $e->getMessage());
        }
    }

    public function backup(string $router)
    {
        try {
            $cfg     = $this->svc->routerConfig($router);
            $content = $this->svc->downloadBackup($router);
            $name    = Str::slug($cfg['name']) . '-' . date('Y-m-d-His') . '.backup';

            return response($content, 200, [
                'Content-Type'        => 'application/octet-stream',
                'Content-Disposition' => "attachment; filename={$name}",
                'Content-Length'      => strlen($content),
            ]);
        } catch (Exception $e) {
            return back()->with('error', 'Gagal backup: ' . $e->getMessage());
        }
    }
}
