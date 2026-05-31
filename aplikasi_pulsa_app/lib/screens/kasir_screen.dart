import 'package:flutter/material.dart';
import 'package:dio/dio.dart';
import '../services/api_service.dart';
import '../widgets/custom_sidebar.dart';

class KasirScreen extends StatefulWidget {
  const KasirScreen({super.key});

  @override
  State<KasirScreen> createState() => _KasirScreenState();
}

class _KasirScreenState extends State<KasirScreen> {
  final ApiService _api = ApiService();
  bool _isLoading = true;
  bool _isProcessing = false;
  String _error = '';

  List<dynamic> _produkFisik = [];
  List<dynamic> _providers = [];
  List<dynamic> _nominals = [];
  List<dynamic> _cart = [];
  Map<String, dynamic> _statistik = {};

  String _selectedProviderId = '';
  String _selectedNominalId = '';
  String _selectedPaymentMethod = 'tunai';

  final TextEditingController _phoneController = TextEditingController();
  final TextEditingController _qtyController = TextEditingController(text: '1');

  @override
  void initState() {
    super.initState();
    _loadKasirData();
  }

  @override
  void dispose() {
    _phoneController.dispose();
    _qtyController.dispose();
    super.dispose();
  }

  Future<void> _loadKasirData() async {
    if (!mounted) return;
    setState(() {
      _isLoading = true;
      _error = '';
    });

    try {
      final response = await _api.getKasirData();
      if (response.statusCode == 200 && response.data['success'] == true) {
        final data = response.data['data'] as Map<String, dynamic>;
        setState(() {
          _produkFisik = data['produk_fisik'] as List<dynamic>;
          final digitalData = data['produk_digital'] as Map<String, dynamic>;
          _providers = digitalData['providers'] as List<dynamic>;
          _nominals = digitalData['nominals'] as List<dynamic>;
          _cart = (data['cart'] as Map<String, dynamic>).values.toList();
          _statistik = data['statistik'] as Map<String, dynamic>? ?? {};
          _isLoading = false;
        });
      } else {
        setState(() {
          _error = response.data['message'] ?? 'Gagal memuat data kasir';
          _isLoading = false;
        });
      }
    } on DioException catch (e) {
      if (e.response?.statusCode == 401) {
        await _api.clearToken();
        if (mounted) Navigator.pushReplacementNamed(context, '/login');
        return;
      }
      setState(() {
        _error = 'Koneksi gagal: ${e.message}';
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = 'Terjadi kesalahan: $e';
        _isLoading = false;
      });
    }
  }

  Future<void> _addFisikProduct(String produkId, int jumlah) async {
    if (!mounted) return;
    setState(() => _isProcessing = true);
    try {
      final response = await _api.addKasirFisik(produkId, jumlah);
      if (response.statusCode == 200 && response.data['success'] == true) {
        await _loadKasirData();
      } else {
        _showMessage(response.data['message'] ?? 'Gagal menambahkan produk');
      }
    } on DioException catch (e) {
      _showMessage(e.response?.data['message'] ?? 'Koneksi gagal');
    } finally {
      if (mounted) setState(() => _isProcessing = false);
    }
  }

  Future<void> _addDigitalProduct() async {
    final providerId = int.tryParse(_selectedProviderId);
    final nominalId = int.tryParse(_selectedNominalId);
    final phone = _phoneController.text.trim();

    if (providerId == null || nominalId == null || phone.isEmpty) {
      _showMessage('Lengkapi provider, nominal, dan nomor tujuan');
      return;
    }

    if (!mounted) return;
    setState(() => _isProcessing = true);

    try {
      final response = await _api.addKasirDigital(phone, providerId, nominalId, _selectedPaymentMethod);
      if (response.statusCode == 200 && response.data['success'] == true) {
        await _loadKasirData();
      } else {
        _showMessage(response.data['message'] ?? 'Gagal menambahkan pulsa digital');
      }
    } on DioException catch (e) {
      _showMessage(e.response?.data['message'] ?? 'Koneksi gagal');
    } finally {
      if (mounted) setState(() => _isProcessing = false);
    }
  }

  Future<void> _removeCartItem(String itemId) async {
    if (!mounted) return;
    setState(() => _isProcessing = true);
    try {
      final response = await _api.removeKasirItem(itemId);
      if (response.statusCode == 200 && response.data['success'] == true) {
        await _loadKasirData();
      } else {
        _showMessage(response.data['message'] ?? 'Gagal menghapus item');
      }
    } on DioException catch (e) {
      _showMessage(e.response?.data['message'] ?? 'Koneksi gagal');
    } finally {
      if (mounted) setState(() => _isProcessing = false);
    }
  }

  Future<void> _checkoutKasir() async {
    if (!mounted) return;
    if (_cart.isEmpty) {
      _showMessage('Keranjang kosong');
      return;
    }

    final total = _cart.fold<num>(0, (sum, item) => sum + (item['subtotal'] ?? 0));
    setState(() => _isProcessing = true);

    try {
      final response = await _api.checkoutKasir(
        metodePembayaran: _selectedPaymentMethod,
        ppnPercent: 0,
        ppn: 0,
        diskon: 0,
        grandTotal: total.toDouble(),
      );

      if (response.statusCode == 200 && response.data['success'] == true) {
        _showMessage('Checkout berhasil: ${response.data['data']?['invoice'] ?? ''}');
        await _loadKasirData();
      } else {
        _showMessage(response.data['message'] ?? 'Gagal checkout');
      }
    } on DioException catch (e) {
      _showMessage(e.response?.data['message'] ?? 'Koneksi gagal');
    } finally {
      if (mounted) setState(() => _isProcessing = false);
    }
  }

  void _showMessage(String message) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(message)));
  }

  void _onSidebarItemSelected(int index) async {
    final roleId = await _api.getUserRole();
    switch (index) {
      case 0:
        Navigator.pushReplacementNamed(context, '/pulsa-provider');
        break;
      case 1:
        Navigator.pushReplacementNamed(context, '/topup-saldo');
        break;
      case 2:
        Navigator.pushReplacementNamed(context, '/scan-pulsa');
        break;
      case 3:
        Navigator.pushReplacementNamed(context, '/history');
        break;
      default:
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Fitur belum tersedia')),
        );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      drawer: CustomSidebar(selectedIndex: 0, onItemSelected: _onSidebarItemSelected),
      appBar: AppBar(title: const Text('Kasir')),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error.isNotEmpty
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text(_error),
                      const SizedBox(height: 16),
                      ElevatedButton(onPressed: _loadKasirData, child: const Text('Coba Lagi')),
                    ],
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _loadKasirData,
                  child: SingleChildScrollView(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            _buildStatCard('Fisik', _produkFisik.length.toString(), Colors.blue),
                            const SizedBox(width: 12),
                            _buildStatCard('Pulsa', _cart.where((item) => item['jenis'] == 'digital').length.toString(), Colors.green),
                          ],
                        ),
                        const SizedBox(height: 20),
                        const Text('Produk Fisik', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                        const SizedBox(height: 8),
                        if (_produkFisik.isEmpty)
                          const Text('Tidak ada produk fisik tersedia.')
                        else
                          Column(
                            children: _produkFisik.map((produk) {
                              final price = produk['harga'] ?? 0;
                              return Card(
                                margin: const EdgeInsets.only(bottom: 12),
                                child: ListTile(
                                  title: Text(produk['nama_produk'] ?? ''),
                                  subtitle: Text('Stok: ${produk['stok'] ?? 0} • Rp ${price.toString()}'),
                                  trailing: ElevatedButton(
                                    onPressed: () {
                                      _qtyController.text = '1';
                                      showDialog<void>(
                                        context: context,
                                        builder: (_) => AlertDialog(
                                          title: const Text('Jumlah Produk'),
                                          content: TextField(
                                            controller: _qtyController,
                                            keyboardType: TextInputType.number,
                                            decoration: const InputDecoration(labelText: 'Jumlah'),
                                          ),
                                          actions: [
                                            TextButton(onPressed: () => Navigator.pop(context), child: const Text('Batal')),
                                            ElevatedButton(
                                              onPressed: () {
                                                final qty = int.tryParse(_qtyController.text) ?? 0;
                                                if (qty > 0) {
                                                  Navigator.pop(context);
                                                  _addFisikProduct(produk['id'].toString(), qty);
                                                } else {
                                                  _showMessage('Isi jumlah yang valid');
                                                }
                                              },
                                              child: const Text('Tambah'),
                                            ),
                                          ],
                                        ),
                                      );
                                    },
                                    child: const Text('Tambah'),
                                  ),
                                ),
                              );
                            }).toList(),
                          ),
                        const SizedBox(height: 20),
                        const Text('Pulsa Digital', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                        const SizedBox(height: 12),
                        DropdownButtonFormField<String>(
                          value: _selectedProviderId.isNotEmpty ? _selectedProviderId : null,
                          decoration: const InputDecoration(labelText: 'Provider'),
                          items: _providers.map((provider) {
                            return DropdownMenuItem(
                              value: provider['id']?.toString(),
                              child: Text(provider['nama_provider'] ?? ''),
                            );
                          }).toList(),
                          onChanged: (value) => setState(() => _selectedProviderId = value ?? ''),
                        ),
                        const SizedBox(height: 12),
                        DropdownButtonFormField<String>(
                          value: _selectedNominalId.isNotEmpty ? _selectedNominalId : null,
                          decoration: const InputDecoration(labelText: 'Nominal'),
                          items: _nominals.map((nominal) {
                            return DropdownMenuItem(
                              value: nominal['id']?.toString(),
                              child: Text('Rp ${nominal['nominal']?.toString() ?? ''}'),
                            );
                          }).toList(),
                          onChanged: (value) => setState(() => _selectedNominalId = value ?? ''),
                        ),
                        const SizedBox(height: 12),
                        TextField(
                          controller: _phoneController,
                          keyboardType: TextInputType.phone,
                          decoration: const InputDecoration(labelText: 'Nomor Tujuan Pulsa'),
                        ),
                        const SizedBox(height: 12),
                        DropdownButtonFormField<String>(
                          value: _selectedPaymentMethod,
                          decoration: const InputDecoration(labelText: 'Metode Pembayaran'),
                          items: const [
                            DropdownMenuItem(value: 'tunai', child: Text('Tunai')),
                            DropdownMenuItem(value: 'saldo', child: Text('Saldo')),
                            DropdownMenuItem(value: 'transfer', child: Text('Transfer')),
                            DropdownMenuItem(value: 'grip', child: Text('GRIP')),
                          ],
                          onChanged: (value) => setState(() => _selectedPaymentMethod = value ?? 'tunai'),
                        ),
                        const SizedBox(height: 10),
                        ElevatedButton(
                          onPressed: _isProcessing ? null : _addDigitalProduct,
                          child: const Text('Tambah Pulsa ke Keranjang'),
                        ),
                        const SizedBox(height: 20),
                        const Text('Keranjang', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                        const SizedBox(height: 12),
                        if (_cart.isEmpty)
                          const Text('Keranjang kosong')
                        else
                          Column(
                            children: _cart.map((item) {
                              return Card(
                                margin: const EdgeInsets.only(bottom: 12),
                                child: ListTile(
                                  title: Text(item['nama'] ?? ''),
                                  subtitle: Text('Jumlah: ${item['jumlah']} • Subtotal: Rp ${item['subtotal']}'),
                                  trailing: IconButton(
                                    icon: const Icon(Icons.delete_outline, color: Colors.red),
                                    onPressed: () => _removeCartItem(item['id'].toString()),
                                  ),
                                ),
                              );
                            }).toList(),
                          ),
                        const SizedBox(height: 20),
                        ElevatedButton(
                          onPressed: _isProcessing ? null : _checkoutKasir,
                          child: const Text('Checkout'),
                        ),
                        const SizedBox(height: 16),
                      ],
                    ),
                  ),
                ),
    );
  }

  Widget _buildStatCard(String label, String value, Color color) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(16),
          color: color.withOpacity(0.1),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(label, style: TextStyle(color: color, fontWeight: FontWeight.bold)),
            const SizedBox(height: 8),
            Text(value, style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: color)),
          ],
        ),
      ),
    );
  }
}