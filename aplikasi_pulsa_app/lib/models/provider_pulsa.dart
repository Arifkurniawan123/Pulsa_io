class ProviderPulsa {
  final int id;
  final String namaProvider;
  final String kodeProvider;
  final String status;

  ProviderPulsa({
    required this.id,
    required this.namaProvider,
    required this.kodeProvider,
    required this.status,
  });

  factory ProviderPulsa.fromJson(Map<String, dynamic> json) {
    return ProviderPulsa(
      id: int.parse(json['id'].toString()),
      namaProvider: json['nama_provider'] ?? '',
      kodeProvider: json['kode_provider'] ?? '',
      status: json['status'] ?? 'active',
    );
  }
}