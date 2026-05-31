class ScannedPulsaEntry {
  final int id;
  final String noTujuan;
  final String namaProvider;
  final int providerId;
  final int nominalId;
  final int nominal;
  final String metodePembayaran;
  final String status;
  final DateTime createdAt;

  ScannedPulsaEntry({
    required this.id,
    required this.noTujuan,
    required this.namaProvider,
    required this.providerId,
    required this.nominalId,
    required this.nominal,
    required this.metodePembayaran,
    required this.status,
    required this.createdAt,
  });

  factory ScannedPulsaEntry.fromJson(Map<String, dynamic> json) {
    return ScannedPulsaEntry(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id']?.toString() ?? '') ?? 0,
      noTujuan: json['no_tujuan'] ?? '',
      namaProvider: json['nama_provider'] ?? 'Unknown',
      providerId: json['provider_id'] is int 
          ? json['provider_id']
          : int.tryParse(json['provider_id']?.toString() ?? '') ?? 0,
      nominalId: json['nominal_id'] is int
          ? json['nominal_id']
          : int.tryParse(json['nominal_id']?.toString() ?? '') ?? 0,
      nominal: json['nominal'] is int
          ? json['nominal']
          : int.tryParse(json['nominal']?.toString() ?? '') ?? 0,
      metodePembayaran: json['metode_pembayaran'] ?? 'tunai',
      status: json['status'] ?? 'sukses',
      createdAt: DateTime.tryParse(json['created_at'] ?? '') ?? DateTime.now(),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'no_tujuan': noTujuan,
      'provider_id': providerId,
      'nominal_id': nominalId,
      'nominal': nominal,
      'metode_pembayaran': metodePembayaran,
      'status': status,
      'created_at': createdAt.toIso8601String(),
    };
  }

  String get nominalFormatted {
    return 'Rp ${nominal.toString().replaceAllMapped(RegExp(r'\B(?=(\d{3})+(?!\d))'), (m) => '.')}';
  }

  String get statusLabel {
    switch (status) {
      case 'sukses':
      case 'berhasil':
        return '✓ Sukses';
      case 'gagal':
        return '✗ Gagal';
      case 'pending':
        return '⊙ Pending';
      default:
        return status;
    }
  }
}