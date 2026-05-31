class NominalPulsa {
  final int id;
  final int providerId;
  final double nominal;
  final double hargaModal;
  final double hargaJual;
  final String status;

  NominalPulsa({
    required this.id,
    required this.providerId,
    required this.nominal,
    required this.hargaModal,
    required this.hargaJual,
    required this.status,
  });

  factory NominalPulsa.fromJson(Map<String, dynamic> json) {
    return NominalPulsa(
      id: int.parse(json['id'].toString()),
      providerId: int.parse(json['provider_id'].toString()),
      nominal: double.tryParse(json['nominal'].toString()) ?? 0,
      hargaModal: double.tryParse(json['harga_modal'].toString()) ?? 0,
      hargaJual: double.tryParse(json['harga_jual'].toString()) ?? 0,
      status: json['status'] ?? 'active',
    );
  }

  String get nominalLabel {
    final n = nominal.toInt();
    if (n >= 1000000) return 'Rp ${(n / 1000000).toStringAsFixed(0)} jt';
    if (n >= 1000) return 'Rp ${(n / 1000).toStringAsFixed(0)} rb';
    return 'Rp $n';
  }

  String get hargaJualLabel {
    return 'Rp ${hargaJual.toStringAsFixed(0).replaceAllMapped(
      RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'),
      (m) => '${m[1]}.',
    )}';
  }
}