class TopupSaldoHistory {
  final String id; // String, bukan int
  final String userId;
  final int nominal;
  final String metodePembayaran;
  final String tipeTransaksi;
  final String status;
  final String referensiId;
  final String keterangan;
  final DateTime createdAt;

  TopupSaldoHistory({
    required this.id,
    required this.userId,
    required this.nominal,
    required this.metodePembayaran,
    required this.tipeTransaksi,
    required this.status,
    required this.referensiId,
    required this.keterangan,
    required this.createdAt,
  });

  factory TopupSaldoHistory.fromJson(Map<String, dynamic> json) {
    return TopupSaldoHistory(
      id: json['id']?.toString() ?? '',
      userId: json['user_id']?.toString() ?? '',
      nominal: int.tryParse(json['nominal'].toString()) ?? 0,
      metodePembayaran: json['metode_pembayaran'] ?? 'tunai',
      tipeTransaksi: json['tipe_transaksi'] ?? 'topup_saldo',
      status: json['status'] ?? 'pending',
      referensiId: json['referensi_id'] ?? '',
      keterangan: json['keterangan'] ?? '',
      createdAt: DateTime.tryParse(json['created_at'] ?? '') ?? DateTime.now(),
    );
  }

  String get nominalFormatted {
    return 'Rp ${nominal.toString().replaceAllMapped(RegExp(r'\B(?=(\d{3})+(?!\d))'), (m) => '.')}';
  }

  String get statusLabel {
    switch (status) {
      case 'berhasil':
        return '✓ Berhasil';
      case 'gagal':
        return '✗ Gagal';
      case 'pending':
        return '⊙ Pending';
      default:
        return status;
    }
  }

  String get tipeTransaksiLabel {
    return tipeTransaksi == 'topup_saldo' ? 'Top-up Saldo' : 'Top-up Pulsa';
  }
}