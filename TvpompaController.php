<?php
namespace App\Http\Controllers\Trs\Tv;

use App\Http\Controllers\Controller;
use App\Model\Sys\Syplant;
use App\Model\Trs\Local\Niotpompa;
use App\Sf;
use DB;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TvpompaController extends Controller
{
    public function index(Request $request)
    {
        if (!$plant = Sf::isPlant()) {
            return Sf::selectPlant();
        }
        $last = DB::connection('sensor2201db')->select("SELECT * FROM t_pompa ORDER BY id DESC LIMIT 1");

        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        $monthlyData = Niotpompa::select(DB::raw("DATE(tanggal) as date"), DB::raw("SUM(TIME_TO_SEC(durasi)) as total_durasi"))
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->groupBy(DB::raw("DATE(tanggal)"))
            ->get();
        $dateLabels = range(1, 30);

        $dailyData = [];
        foreach ($dateLabels as $dayOfMonth) {
            $dailyData[] = ['label' => (string) $dayOfMonth, 'value' => 0];
        }
        foreach ($monthlyData as $data) {
            $totalDurasiMenit = round($data->total_durasi / 60);
            $dayOfMonth = intval(substr($data->date, 8, 2));
            $dailyData[$dayOfMonth - 1]['value'] = $totalDurasiMenit;
        }
        foreach ($dailyData as &$item) {
            $item['showValue'] = true;
        }
        $chartDataJson = json_encode(array_values($dailyData));

        Sf::log("trs_tv_pompa", "NiotpompaController@" . __FUNCTION__, "Open Page  ", "link");

        return view('trs.tv.tvpompa.tvpompa', compact(['request', 'plant', 'last', 'chartDataJson']));
    }

    public function getList(Request $request)
    {
        $request->q = str_replace(" ", "%", $request->q);

        $data = Niotpompa::where(function ($q) use ($request) {
            $q->orWhere('id', 'like', "%" . @$request->q . "%");
        })
            ->whereMonth('tanggal', '=', now()->month) // Filter berdasarkan bulan saat ini
            ->orderBy(isset($request->order_by) ? substr($request->order_by, 1) : 'tanggal', substr(@$request->order_by, 0, 1) == '-' ? 'asc' : 'desc');

        if ($request->trash == 1) {
            $data = $data->onlyTrashed();
        }

        $data = $data->paginate(isset($request->limit) ? $request->limit : 31);

        $data->getCollection()->transform(function ($value) {
            //isikan transformasi disini
            $value->token = Sf::encrypt($value->id);
            return $value;
        });

        return response()->json(compact(['data']));
    }

    public function getLookup(Request $request)
    {
        $request->q = str_replace(" ", "%", $request->q);
        $data = Niotpompa::where(function ($q) use ($request) {
            $q->orWhere('id', 'like', "%" . @$request->q . "%");
        })
            //->where('plant',$request->plant)
            ->orderBy(isset($request->order_by) ? substr($request->order_by, 1) : 'id', substr(@$request->order_by, 0, 1) == '-' ? 'desc' : 'asc');
        $data = $data->paginate(isset($request->limit) ? $request->limit : 30);
        return view('sys.system.dialog.sflookup', compact(['data', 'request']));
    }

    public function store(Request $request)
    {
        $req = json_decode(request()->getContent());
        $h = $req->h;
        $f = $req->f;

        try {
            $arr = array_merge((array) $h, ['plant' => $f->plant, 'updated_at' => date('Y-m-d H:i:s')]);
            if ($f->crud == 'c') {
                // if (!Sf::allowed('TRS_TV_POMPA_C')) {
                // 	return response()->json(Sf::reason(), 401);
                // }
                $data = new Niotpompa();
                $arr = array_merge($arr, ['created_by' => Auth::user()->userid, 'created_at' => date('Y-m-d H:i:s')]);
                $data->create($arr);
                $id = DB::getPdo()->lastInsertId();
                Sf::log("trs_tv_pompa", $id, "Create Pompa (niotpompa) id : " . $id, "create");
                return response()->json('created');
            } else {
                // if (!Sf::allowed('TRS_TV_POMPA_U')) {
                // 	return response()->json(Sf::reason(), 401);
                // }
                $id = Sf::decrypt($h->token);
                $data = Niotpompa::find($id);
                if ($data === null) {
                    return response()->json("error token", 400);
                }
                $data->update($arr);
                $id = $data->id;
                Sf::log("trs_tv_pompa", $id, "Update Pompa (niotpompa) id : " . $id, "update");
                return response()->json('updated');
            }

        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function edit($token)
    {
        $id = Sf::decrypt($token);
        $h = Niotpompa::where('id', $id)->withTrashed()->first();
        if ($h === null) {
            return response()->json("error token", 400);
        }
        $h->token = $token;
        return response()->json(compact(['h']));
    }

    public function destroy($token, Request $request)
    {
        try {
            $id = Sf::decrypt($token);
            $data = Niotpompa::where('id', $id)->withTrashed()->first();
            if ($data === null) {
                return response()->json("error token", 400);
            }
            if ($request->restore == 1) {
                // if (!Sf::allowed('TRS_TV_POMPA_S')) {
                // 	return response()->json(Sf::reason(), 401);
                // }
                $data->restore();
                Sf::log("trs_tv_pompa", $id, "Restore Pompa (niotpompa) id : " . $id, "restore");
                return response()->json('restored');
            } else {
                // if (!Sf::allowed('TRS_TV_POMPA_D')) {
                // 	return response()->json(Sf::reason(), 401);
                // }
                $data->delete();
                Sf::log("trs_tv_pompa", $id, "Delete Pompa (niotpompa) id : " . $id, "delete");
                return response()->json('deleted');
            }
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function TotalWaktu()
    {

        $waktu = DB::connection('sensor2201db')->select('SELECT DATE(waktu) AS tanggal, SEC_TO_TIME(SUM(TIMESTAMPDIFF(SECOND, waktu, (SELECT MIN(waktu) FROM t_pompa WHERE STATUS = "OFF" AND waktu > t.waktu)))) AS durasi FROM t_pompa t WHERE STATUS = "ON" AND DATE(waktu) = CURDATE() GROUP BY DATE(waktu);');
        if (!empty($waktu)) {
            // dd($waktu);
            $totalDurasi = $waktu[0]->durasi;

            $total = DB::connection('mysql')->table('niotpompa')->insert([
                'tanggal' => $waktu[0]->tanggal,
                'durasi' => $totalDurasi,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($total) {
                echo "Data berhasil diinsert.";
            } else {
                echo "Gagal melakukan insert data.";
            }
        } else {
            echo "Tidak ada data untuk diinsert.";
        }

        // // Lakukan query untuk mendapatkan data
        // $result = DB::connection('sensor2201db')->select('SELECT DATE(waktu) AS tanggal, SUM(TIMESTAMPDIFF(SECOND, waktu, (SELECT MIN(waktu) FROM t_pompa WHERE status = "OFF" AND waktu > t.waktu))) AS durasi_pompa_on FROM t_pompa t WHERE status = "ON" GROUP BY DATE(waktu);');

        // // Simpan data ke dalam tabel total_waktu menggunakan Eloquent
        // foreach ($result as $row) {
        //     TotalWaktu::create([
        //         'tanggal' => $row->tanggal,
        //         'total_waktu' => $row->total_waktu,
        //     ]);
        // }

        // // Tampilkan data yang disimpan
        // $totalWaktu = TotalWaktu::all();
        // dd($totalWaktu);



        // $waktu = DB::connection('sensor2201db')->select('SELECT DATE(waktu) AS tanggal, SUM(TIMESTAMPDIFF(SECOND, waktu, (SELECT MIN(waktu) FROM t_pompa WHERE status = "OFF" AND waktu > t.waktu))) AS durasi_pompa_on FROM t_pompa t WHERE status = "ON" GROUP BY DATE(waktu);');

        // $total = DB::connection('mysql')->

        // $waktu = DB::connection('sensor2201db')->select('SELECT DATE(waktu) AS tanggal, SUM(TIMESTAMPDIFF(SECOND, waktu, (SELECT MIN(waktu) FROM t_pompa WHERE STATUS = "OFF" AND waktu > t.waktu))) AS durasi_pompa_on FROM t_pompa t WHERE STATUS = "ON" AND DATE(waktu) = CURDATE()GROUP BY DATE(waktu);');
        // dd($waktu);

    }

    public function sendNotif(Request $request)
    {
        $this->sendNotification('2201');
        $this->TotalWaktu('2201');

    }
    public function sendNotification($plant)
    {
        $time = date('Y-m-d');
        $current = DB::connection('sensor2201db')->select("SELECT * FROM t_pompa ORDER BY id DESC LIMIT 1");
        $parsys = Sf::getParsys('IOT_POMPA_NOTIF_WA', @$plant);
        $str = explode(",", $parsys);

        $dt = Carbon::now();

        if (@$current[0]->status == 'MALF') {

            $msg = "_*🔔 Pump Notification : ❗MALFUNCTION !!!❗*_\n";
            $msg .= "========================================\n\n";

            $msg .= "Perhatian !!! Pompa tidak menyala, ";
            $msg .= "\n";
            $msg .= "Jam : " . $current[0]->waktu;

            $msg .= "\n";
            $msg .= "\n";
            $parsys = Sf::getParsys('IOT_POMPA_NOTIF_WA', @$plant);
            $str = explode(",", $parsys);
            foreach ($str as $k => $v) {

                $val = explode("|", $v);
                ob_start();
                $ret = Sf::waSend(@$val[0], $msg, @$plant, 'trs_tv_pompa', '1');
                ob_clean();
            }
        }

    }
    public function ActionNotif(Request $request)
    {

        $this->sendNotification("2201");
    }


    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    // public function index(Request $request)
    // {
    //     if (!$plant = Sf::isPlant()) {
    //         return Sf::selectPlant();
    //     }

    //      $lastStatus = $this->lastStatus();
    //     return view('trs.tv.tvpompa.tvpompa', compact(['request', 'plant','lastStatus']));
    // }

    // public function renderData(Request $request)
    // {
    //     $filterDate = date('Y-m', strtotime($request->month));
    //     $last = date('t', strtotime($request->month));


    //     // Example Query
    //     $h = DB::connection('sensor2201db')->select("SELECT * FROM t_grecon where waktu LIKE '$filterDate%'");

    //     $lastStatus = $this->lastStatus();
    //     return response()->json(compact(['lastStatus']));
    // }

    // public function lastStatus(){
    //     $last = DB::connection('sensor2201db')->select("SELECT status FROM t_pompa ORDER BY id DESC LIMIT 1");
    //     return $last;
    // }
}
?>