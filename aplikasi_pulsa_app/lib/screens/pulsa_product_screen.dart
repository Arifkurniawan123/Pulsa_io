import 'package:flutter/material.dart';
import 'package:dio/dio.dart';
import '../services/api_service.dart';
import '../models/digiflazz_product.dart';
import 'topup_screen.dart';

class PulsaProductScreen extends StatefulWidget {
  final String brand;
  final String category;
  const PulsaProductScreen({super.key, required this.brand, required this.category});

  @override
  State<PulsaProductScreen> createState() => _PulsaProductScreenState();
}

class _PulsaProductScreenState extends State<PulsaProductScreen> {
  final ApiService _api = ApiService();
  List<DigiflazzProduct> _products = [];
  bool _isLoading = true;
  String _error = '';

  @override
  void initState() {
    super.initState();
    _fetchProducts();
  }

  Future<void> _fetchProducts({bool forceRefresh = false}) async {
    if (!mounted) return;
    setState(() => _isLoading = true);
    try {
      final response = await _api.getDigiflazzPricelist(forceRefresh: forceRefresh);
      if (response.statusCode == 200 && response.data != null) {
        final dynamic data = response.data['data'];
        // Cek error limitasi
        if (data is Map && data.containsKey('rc') && data['rc'] == '83') {
          setState(() {
            _error = 'Server sibuk, coba beberapa saat lagi.';
            _isLoading = false;
          });
          return;
        }
        final List products = data is List ? data : [];
        final filtered = products.where((p) =>
            p['brand'] == widget.brand &&
            p['category'] == widget.category &&
            p['buyer_product_status'] == true).toList();
        _products = filtered.map((e) => DigiflazzProduct.fromJson(e)).toList();
        if (mounted) setState(() => _isLoading = false);
      } else {
        if (mounted) setState(() {
          _error = 'Gagal mengambil produk';
          _isLoading = false;
        });
      }
    } on DioException catch (e) {
      if (mounted) setState(() {
        _error = 'Koneksi gagal: ${e.message}';
        _isLoading = false;
      });
    }
  }

  String _formatRupiah(double price) {
    return 'Rp ${price.toStringAsFixed(0).replaceAllMapped(RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), (m) => '${m[1]}.')}';
  }

  double _getHargaJual(double modal) => modal * 1.05;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('${widget.brand} - ${widget.category}')),
      body: RefreshIndicator(
        onRefresh: () => _fetchProducts(forceRefresh: true),
        child: _isLoading
            ? const Center(child: CircularProgressIndicator())
            : _error.isNotEmpty
                ? Center(child: Text(_error))
                : _products.isEmpty
                    ? const Center(child: Text('Tidak ada produk untuk provider ini'))
                    : ListView.builder(
                        itemCount: _products.length,
                        itemBuilder: (context, index) {
                          final p = _products[index];
                          final hargaJual = _getHargaJual(p.price);
                          return Card(
                            margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                            child: ListTile(
                              title: Text(p.productName),
                              subtitle: Text(_formatRupiah(hargaJual)),
                              trailing: ElevatedButton(
                                onPressed: () {
                                  Navigator.push(
                                    context,
                                    MaterialPageRoute(
                                      builder: (_) => TopupScreen(
                                        product: p,
                                        hargaJual: hargaJual,
                                      ),
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