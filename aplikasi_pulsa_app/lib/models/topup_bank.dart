class TopupBank {
  final int id;
  final String refId;
  final String namaBank;
  final String nomorRekening;
  final String atasNama;
  final int nominal;
  final String status;
  final String? keterangan;
  final DateTime createdAt;
  final DateTime? updatedAt;

  TopupBank({
    required this.id,
    required this.refId,
    required this.namaBank,
    required this.nomorRekening,
    required this.atasNama,
    required this.nominal,
    required this.status,
    this.keterangan,
    required this.createdAt,
    this.updatedAt,
  });

  factory TopupBank.fromJson(Map<String, dynamic> json) {
    return TopupBank(
      id: json['id'] ?? 0,
      refId: json['ref_id'] ?? '',
      namaBank: json['nama_bank'] ?? '',
      nomorRekening: json['nomor_rekening'] ?? '',
      atasNama: json['atas_nama'] ?? '',
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
