import 'package:flutter/material.dart';
import 'package:dio/dio.dart';
import '../services/api_service.dart';
import '../models/nominal_pulsa.dart';
import 'topup_screen.dart';

class PulsaProductScreen extends StatefulWidget {
  final int providerId;
  final String namaProvider;
  const PulsaProductScreen({super.key, required this.providerId, required this.namaProvider});

  @override
  State<PulsaProductScreen> createState() => _PulsaProductScreenState();
}

class _PulsaProductScreenState extends State<PulsaProductScreen> {
  final ApiService _api = ApiService();
  List<NominalPulsa> _nominals = [];
  bool _isLoading = true;
  String _error = '';

  @override
  void initState() {
    super.initState();
    _fetchNominals();
  }

  Future<void> _fetchNominals() async {
    if (!mounted) return;
    setState(() {
      _isLoading = true;
      _error = '';
    });
    try {
      final response = await _api.getNominalByProvider(widget.providerId);
      if (response.statusCode == 200 && response.data['success'] == true) {
        final List raw = response.data['data'] as List;
        setState(() {
          _nominals = raw.map((e) => NominalPulsa.fromJson(e)).toList();
          _isLoading = false;
        });
      } else {
        setState(() {
          _error = response.data['message'] ?? 'Gagal mengambil nominal';
          _isLoading = false;
        });
      }
    } on DioException catch (e) {
      if (e.response?.statusCode == 401) {
        await _api.clearToken();
        if (mounted) Navigator.pushReplacementNamed(context, '/login');
      } else {
        setState(() {
          _error = 'Koneksi gagal: ${e.message}';
          _isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Pulsa ${widget.namaProvider}')),
      body: RefreshIndicator(
        onRefresh: _fetchNominals,
        child: _isLoading
            ? const Center(child: CircularProgressIndicator())
            : _error.isNotEmpty
                ? Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Text(_error),
                        const SizedBox(height: 16),
                        ElevatedButton(
                          onPressed: _fetchNominals,
                          child: const Text('Coba Lagi'),
                        ),
                      ],
                    ),
                  )
                : _nominals.isEmpty
                    ? const Center(child: Text('Tidak ada nominal pulsa untuk provider ini'))
                    : ListView.builder(
                        padding: const EdgeInsets.all(16),
                        itemCount: _nominals.length,
                        itemBuilder: (context, index) {
                          final n = _nominals[index];
                          return Card(
                            margin: const EdgeInsets.only(bottom: 10),
                            child: ListTile(
                              title: Text(n.nominalLabel),
                              subtitle: Text('Harga: ${n.hargaJualLabel}'),
                              trailing: ElevatedButton(
                                onPressed: () {
                                  Navigator.push(
                                    context,
                                    MaterialPageRoute(
                                      builder: (_) => TopupScreen(nominal: n),
                                    ),
                                  );
                                },
                                child: const Text('Beli'),
                              ),
                            ),
                          );
                        },
                      ),
      ),
    );
  }
}