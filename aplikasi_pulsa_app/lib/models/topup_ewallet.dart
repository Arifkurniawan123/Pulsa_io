class TopupEwallet {
  final int id;
  final String refId;
  final String metodeEwallet;
  final String nomorTelepon;
  final int nominal;
  final String status;
  final String? keterangan;
  final DateTime createdAt;
  final DateTime? updatedAt;

  TopupEwallet({
    required this.id,
    required this.refId,
    required this.metodeEwallet,
    required this.nomorTelepon,
    required this.nominal,
    required this.status,
    this.keterangan,
    required this.createdAt,
    this.updatedAt,
  });

  factory TopupEwallet.fromJson(Map<String, dynamic> json) {
    return TopupEwallet(
      id: json['id'] ?? 0,
      refId: json['ref_id'] ?? '',
      metodeEwallet: json['metode_ewallet'] ?? '',
      nomorTelepon: json['nomor_telepon'] ?? '',
      nominal: json['nominal'] ?? 0,
      status: json['status'] ?? 'pending',
      keterangan: json['keterangan'],
      createdAt: DateTime.parse(json['created_at'] ?? DateTime.now().toString()),
      updatedAt: json['updated_at'] != null ? DateTime.parse(json['updated_at']) : null,
    );
  }

  String get nominalFormatted {
    final formatter = _formatCurrency(nominal);
    return 'Rp $formatter';
  }

  String get statusLabel {
    switch (status) {
      case 'berhasil':
        return 'Berhasil';
      case 'pending':
        return 'Menunggu';
      case 'proses':
        return 'Proses';
      case 'gagal':
        return 'Gagal';
      default:
        return status;
    }
  }

  String _formatCurrency(int value) {
    return value.toString().replaceAllMapped(RegExp(r'\B(?=(\d{3})+(?!\d))'), (m) => '.');
  }
}
