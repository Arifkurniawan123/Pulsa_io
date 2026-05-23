import 'package:flutter/material.dart';
import 'package:dio/dio.dart';
import '../services/api_service.dart';
import 'pulsa_product_screen.dart';

class PulsaProviderScreen extends StatefulWidget {
  const PulsaProviderScreen({super.key});

  @override
  State<PulsaProviderScreen> createState() => _PulsaProviderScreenState();
}

class _PulsaProviderScreenState extends State<PulsaProviderScreen> {
  final ApiService _api = ApiService();
  List<String> _brands = [];
  bool _isLoading = true;
  String _error = '';

  @override
  void initState() {
    super.initState();
    _fetchProviders();
  }

  Future<void> _fetchProviders({bool forceRefresh = false}) async {
    if (!mounted) return;
    setState(() => _isLoading = true);
    try {
      final response = await _api.getDigiflazzPricelist(forceRefresh: forceRefresh);
      if (response.statusCode == 200 && response.data != null) {
        final dynamic data = response.data['data'];
        // Cek apakah response berupa error limitasi (rc 83)
        if (data is Map && data.containsKey('rc') && data['rc'] == '83') {
          setState(() {
            _error = 'Server sibuk, coba beberapa saat lagi.';
            _isLoading = false;
          });
          return;
        }
        final List products = data is List ? data : [];
        final pulsaProducts = products.where((p) =>
            p['category'] == 'Pulsa' && p['buyer_product_status'] == true);
        final brands = pulsaProducts.map((p) => p['brand'] as String).toSet().toList();
        if (mounted) {
          setState(() {
            _brands = brands;
            _isLoading = false;
            _error = '';
          });
        }
      } else {
        if (mounted) {
          setState(() {
            _error = 'Gagal mengambil data provider';
            _isLoading = false;
          });
        }
      }
    } on DioException catch (e) {
      if (mounted) {
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
      appBar: AppBar(title: const Text('Pilih Provider Pulsa')),
      body: RefreshIndicator(
        onRefresh: () => _fetchProviders(forceRefresh: true),
        child: _isLoading
            ? const Center(child: CircularProgressIndicator())
            : _error.isNotEmpty
                ? Center(child: Text(_error))
                : _brands.isEmpty
                    ? const Center(child: Text('Tidak ada provider pulsa'))
                    : ListView.builder(
                        itemCount: _brands.length,
                        itemBuilder: (context, index) {
                          final brand = _brands[index];
                          return Card(
                            margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                            child: ListTile(
                              leading: const Icon(Icons.sim_card),
                              title: Text(brand),
                              trailing: const Icon(Icons.chevron_right),
                              onTap: () {
                                Navigator.push(
                                  context,
                                  MaterialPageRoute(
                                    builder: (_) => PulsaProductScreen(
                                      brand: brand,
                                      category: 'Pulsa',
                                    ),
                                  ),
                                );
                              },
                            ),
                          );
                        },
                      ),
      ),
    );
  }
}