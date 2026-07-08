<?php

namespace App\Services;

use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Produk;
use App\Models\PenjualanPulsaModel;
use App\Models\NominalModel;
use App\Models\ProviderModel;
use Config\Database;
use Ramsey\Uuid\Uuid;
use App\Traits\WebSocketTrait;

class Kasir
{
    use WebSocketTrait;

    protected $produkModel;
    protected $penjualanModel;
    protected $penjualanDetailModel;
    protected $penjualanPulsaModel;
    protected $nominalModel;
    protected $providerModel;
    
    public function __construct()
    {
        $this->produkModel = new Produk();
        $this->penjualanModel = new Penjualan();
        $this->penjualanDetailModel = new PenjualanDetail();
        $this->penjualanPulsaModel = new PenjualanPulsaModel();
        $this->nominalModel = new NominalModel();
        $this->providerModel = new ProviderModel();
    }

    /**
     * Get data produk fisik (stok tersedia)
     */
    public function getDataProdukFisik()
    {
        try {
            $data = $this->produkModel->findAllDataWithStokReady();
            return [
                'success' => true,
                'data'    => $data,
            ];
        } catch (\Throwable $th) {
            log_message('error', $th->getMessage());
            return [
                'success' => false,
                'data'    => [],
            ];
        }
    }

    /**
     * Get data untuk produk digital (pulsa)
     */
    public function getDataProdukDigital()
    {
        try {
            $providers = $this->providerModel->where('status', 'active')->findAll();
            $nominals = $this->nominalModel->where('status', 'active')->findAll();

            return [
                'success' => true,
                'data'    => [
                    'providers' => $providers,
                    'nominals'  => $nominals
                ],
            ];
        } catch (\Throwable $th) {
            log_message('error', $th->getMessage());
            return [
                'success' => false,
                'data'    => [],
            ];
        }
    }

    /**
     * Tambah produk fisik ke keranjang
     */
    public function tambahProdukFisikKeKeranjang(string $produkId, int $jumlah): array
    {
        try {
            $produk = $this->produkModel->where('id', $produkId)->first();

            if (!$produk) {
                return [
                    'success' => false,
                    'message' => 'Produk tidak ditemukan.',
                ];
            }

            if ($produk->stok < $jumlah) {
                return [
                    'success' => false,
                    'message' => 'Stok tidak cukup. Stok tersedia: ' . $produk->stok,
                ];
            }

            $cart = session()->get('kasir_cart') ?? [];

            if (isset($cart[$produkId])) {
                $cart[$produkId]['jumlah'] += $jumlah;
                $cart[$produkId]['subtotal'] = $cart[$produkId]['jumlah'] * $produk->harga;
            } else {
                $cart[$produkId] = [
                    'id'       => $produk->id,
                    'nama'     => $produk->nama_produk,
                    'harga'    => $produk->harga,
                    'jumlah'   => $jumlah,
                    'subtotal' => $jumlah * $produk->harga,
                    'jenis'    => 'fisik'
                ];
            }

            session()->set('kasir_cart', $cart);

            $this->produkModel
                ->where('id', $produkId)
                ->set('stok', 'stok - ' . $jumlah, false)
                ->update();

            return [
                'success' => true,
                'message' => 'Produk fisik berhasil ditambahkan ke keranjang.',
            ];
        } catch (\Throwable $th) {
            log_message('error', $th->getMessage());
            return [
                'success' => false,
                'message' => 'Ada Kesalahan: ' . $th->getMessage(),
            ];
        }
    }

    /**
     * Tambah produk digital (pulsa) ke keranjang
     */
    public function tambahProdukDigitalKeKeranjang(array $data): array
    {
        try {
            $nominal = $this->nominalModel->find($data['nominal_id']);
            if (!$nominal) {
                return [
                    'success' => false,
                    'message' => 'Data nominal tidak ditemukan.',
                ];
            }

            $provider = $this->providerModel->find($data['provider_id']);

            $cart = session()->get('kasir_cart') ?? [];
            $cartId = 'digital_' . uniqid();

            $cart[$cartId] = [
                'id'          => $cartId,
                'nama'        => 'Pulsa ' . number_format($nominal['nominal'], 0, ',', '.') . 
                                ' - ' . ($provider['nama_provider'] ?? '') . 
                                ' (' . $data['no_tujuan_pulsa'] . ')',
                'harga'       => $nominal['harga_jual'],
                'jumlah'      => 1,
                'subtotal'    => $nominal['harga_jual'],
                'jenis'       => 'digital',
                'no_tujuan_pulsa'         => $data['no_tujuan_pulsa'],
                'provider_id'             => $data['provider_id'],
                'nominal_id'              => $data['nominal_id'],
                'nominal_value'           => $nominal['nominal'],
                'harga_modal'             => $nominal['harga_modal'],
                'metode_pembayaran_pulsa' => $data['metode_pembayaran_pulsa']
            ];

            session()->set('kasir_cart', $cart);

            return [
                'success' => true,
                'message' => 'Produk digital berhasil ditambahkan ke keranjang.',
            ];
        } catch (\Throwable $th) {
            log_message('error', $th->getMessage());
            return [
                'success' => false,
                'message' => 'Ada Kesalahan: ' . $th->getMessage(),
            ];
        }
    }

    /**
     * Hapus item dari keranjang
     */
    public function hapusDariKeranjang(string $itemId): array
    {
        try {
            $cart = session()->get('kasir_cart') ?? [];

            if (!isset($cart[$itemId])) {
                return [
                    'success' => false,
                    'message' => 'Item tidak ditemukan di keranjang.',
                ];
            }

            $item = $cart[$itemId];

            if ($item['jenis'] === 'fisik') {
                $this->produkModel
                    ->where('id', $item['id'])
                    ->set('stok', 'stok + ' . $item['jumlah'], false)
                    ->update();
            }

            unset($cart[$itemId]);
            session()->set('kasir_cart', $cart);

            return [
                'success' => true,
                'message' => 'Item berhasil dihapus dari keranjang.',
            ];
        } catch (\Throwable $th) {
            log_message('error', $th->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $th->getMessage(),
            ];
        }
    }

    /**
     * Checkout semua item di keranjang (fisik dan digital)
     */
    public function checkout(array $data): array
    {
        $db = Database::connect();
        $db->transStart();

        try {
            $cart = session()->get('kasir_cart') ?? [];
            $userId = $data['created_by'];

            if (empty($cart)) {
                throw new \Exception('Tidak ada item di keranjang.');
            }

            $penjualanId = Uuid::uuid4()->toString();
            $noInvoice = 'INV-' . strtoupper(substr(uniqid(), -6));

            $total = 0;
            foreach ($cart as $item) {
                $total += $item['subtotal'];
            }

            // Simpan transaksi penjualan
            $this->penjualanModel->insert([
                'id'          => $penjualanId,
                'no_invoice'  => $noInvoice,
                'created_by'  => $userId,
                'total'       => $total,
                'ppn'         => $data['ppn'] ?? 0,
                'diskon'      => $data['diskon'] ?? 0,
                'metode_pembayaran' => $data['metode'],
                'status'      => 'berhasil',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);

            // Proses setiap item di keranjang
            foreach ($cart as $item) {
                if ($item['jenis'] === 'fisik') {
                    $this->penjualanDetailModel->insert([
                        'id'           => Uuid::uuid4()->toString(),
                        'penjualan_id' => $penjualanId,
                        'produk_id'    => $item['id'],
                        'jumlah'       => $item['jumlah'],
                        'harga'        => $item['harga'],
                        'sub_total'    => $item['subtotal'],
                        'created_at'   => date('Y-m-d H:i:s'),
                        'updated_at'   => date('Y-m-d H:i:s'),
                    ]);
                } elseif ($item['jenis'] === 'digital') {
                    $noTransaksiPulsa = $this->penjualanPulsaModel->generateNoTransaksi();
                    
                    $this->penjualanPulsaModel->insert([
                        'no_transaksi'  => $noTransaksiPulsa,
                        'no_tujuan'     => $item['no_tujuan_pulsa'],
                        'provider_id'   => $item['provider_id'],
                        'nominal_id'    => $item['nominal_id'],
                        'nominal'       => $item['nominal_value'],
                        'harga_modal'   => $item['harga_modal'],
                        'harga_jual'    => $item['harga'],
                        'keuntungan'    => $item['harga'] - $item['harga_modal'],
                        'metode_pembayaran' => $item['metode_pembayaran_pulsa'],
                        'status'        => 'sukses',
                        'created_by'    => $userId,
                        'created_at'    => date('Y-m-d H:i:s'),
                        'updated_at'    => date('Y-m-d H:i:s'),
                    ]);
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return [
                    'success' => false,
                    'message' => 'Gagal menyimpan transaksi.',
                ];
            }

            // 🔥 KIRIM NOTIFIKASI KE WEBSOCKET
            $this->sendWebSocketNotification([
                'type'        => 'produk',
                'no_invoice'  => $noInvoice,
                'total'       => (int) $total,
                'kasir'       => session()->get('name') ?? 'Kasir',
                'items'       => array_values(array_map(function($item) {
                    return $item['nama'] . ' x' . $item['jumlah'];
                }, $cart)),
                'created_at'  => date('H:i:s')
            ]);

            // Kosongkan keranjang
            session()->remove('kasir_cart');

            return [
                'success' => true,
                'message' => 'Transaksi berhasil disimpan. No. Invoice: ' . $noInvoice,
                'data' => [
                    'invoice' => $noInvoice,
                    'penjualan_id' => $penjualanId
                ]
            ];

        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', $e->getMessage());

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get statistik harian
     */
    public function getStatistikHarian()
    {
        try {
            $today = date('Y-m-d');
            
            $transaksiFisik = $this->penjualanModel
                ->where('DATE(created_at)', $today)
                ->countAllResults();

            $transaksiPulsa = $this->penjualanPulsaModel
                ->where('DATE(created_at)', $today)
                ->countAllResults();

            $pendapatanFisik = $this->penjualanModel
                ->selectSum('total')
                ->where('DATE(created_at)', $today)
                ->first();

            $pendapatanPulsa = $this->penjualanPulsaModel
                ->selectSum('harga_jual')
                ->where('DATE(created_at)', $today)
                ->where('status', 'sukses')
                ->first();

            $totalPendapatan = ($pendapatanFisik->total ?? 0) + ($pendapatanPulsa->harga_jual ?? 0);

            return [
                'success' => true,
                'data' => [
                    'transaksi_fisik'  => $transaksiFisik,
                    'transaksi_pulsa'  => $transaksiPulsa,
                    'total_transaksi'  => $transaksiFisik + $transaksiPulsa,
                    'total_pendapatan' => $totalPendapatan
                ]
            ];
        } catch (\Throwable $th) {
            log_message('error', $th->getMessage());
            return [
                'success' => false,
                'data' => []
            ];
        }
    }
}