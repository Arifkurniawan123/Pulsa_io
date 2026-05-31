import 'package:flutter/material.dart';
import 'package:dio/dio.dart';
import '../services/api_service.dart';
import '../widgets/custom_appbar.dart';
import '../widgets/custom_sidebar.dart';
import '../widgets/custom_footer.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  final ApiService _api = ApiService();
  Map<String, dynamic> _data = {};
  bool _isLoading = true;
  String _error = '';
  String _userName = 'Administrator';

  @override
  void initState() {
    super.initState();
    _loadUserName();
    _loadDashboard();
  }

  Future<void> _loadUserName() async {
    final name = await _api.getUserName();
    if (!mounted) return;
    setState(() => _userName = name?.isNotEmpty == true ? name! : 'Administrator');
  }

  Future<void> _loadDashboard() async {
    setState(() => _isLoading = true);
    try {
      final response = await _api.getDashboard();
      if (response.statusCode == 200 && response.data['success'] == true) {
        setState(() => _data = response.data['data']);
        _error = '';
      } else {
        setState(() => _error = response.data['message'] ?? 'Gagal load data');
      }
    } on DioException catch (e) {
      if (e.response?.statusCode == 401) {
        await _api.clearToken();
        if (mounted) Navigator.pushReplacementNamed(context, '/login');
      } else {
        setState(() => _error = 'Koneksi gagal: ${e.message}');
      }
    } catch (e) {
      setState(() => _error = 'Error: $e');
    } finally {
      setState(() => _isLoading = false);
    }
  }

  String _formatRupiah(dynamic value) {
    num v = 0;
    if (value is num) v = value;
    else if (value is String) v = num.tryParse(value) ?? 0;
    if (v == 0) return 'Rp 0';
    return 'Rp ${v.toStringAsFixed(0).replaceAllMapped(RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), (m) => '${m[1]}.')}';
  }

  num _toNum(dynamic v) {
    if (v is num) return v;
    if (v is String) return num.tryParse(v) ?? 0;
    return 0;
  }

  void _onSidebarItemSelected(int index) async {
    final roleId = await _api.getUserRole();
    switch (index) {
      case 0:
        // sudah di dashboard
        break;
      case 1:
        Navigator.pushReplacementNamed(context, '/pulsa-provider');
        break;
      case 2:
        Navigator.pushReplacementNamed(context, '/topup-saldo');
        break;
      case 3:
        Navigator.pushReplacementNamed(context, '/scan-pulsa');
        break;
      case 4:
        if (roleId == 2) {
          Navigator.pushReplacementNamed(context, '/user');
        }
        break;
      case 5:
        if (roleId == 2) {
          Navigator.pushReplacementNamed(context, '/history');
        }
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
      appBar: CustomAppbar(
        title: 'Dashboard',
        actions: [IconButton(icon: const Icon(Icons.refresh), onPressed: _loadDashboard)],
      ),
      drawer: CustomSidebar(selectedIndex: 0, onItemSelected: _onSidebarItemSelected),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error.isNotEmpty
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text(_error),
                      const SizedBox(height: 16),
                      ElevatedButton(onPressed: _loadDashboard, child: const Text('Coba Lagi')),
                    ],
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _loadDashboard,
                  child: SingleChildScrollView(
                    child: Column(
                      children: [
                        Padding(
                          padding: const EdgeInsets.all(16),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text('Halo, $_userName!', style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold)),
                              const SizedBox(height: 8),
                              Text('Ringkasan bisnis Anda hari ini', style: TextStyle(color: Colors.grey.shade600)),
                              const SizedBox(height: 24),
                              GridView.count(
                                shrinkWrap: true,
                                physics: const NeverScrollableScrollPhysics(),
                                crossAxisCount: 2,
                                crossAxisSpacing: 16,
                                mainAxisSpacing: 16,
                                childAspectRatio: 1.3,
                                children: [
                                  _buildCard('Transaksi Produk', '${_toNum(_data['transaksiHariIni'])}', Icons.receipt, Colors.blue.shade600),
                                  _buildCard('Pendapatan Produk', _formatRupiah(_data['pendapatanHariIni']), Icons.money, Colors.green.shade600),
                                  _buildCard('Produk Terjual', '${_toNum(_data['produkTerjualHariIni'])}', Icons.shopping_cart, Colors.orange.shade600),
                                  _buildCard('Stok Habis', '${_toNum(_data['StokHabis'])}', Icons.warning, Colors.red.shade600),
                                  _buildCard('Transaksi Pulsa', '${_toNum(_data['transaksiPulsaHariIni'])}', Icons.mobile_friendly, Colors.purple.shade600),
                                  _buildCard('Pendapatan Pulsa', _formatRupiah(_data['pendapatanPulsaHariIni']), Icons.money_off, Colors.teal.shade600),
                                  _buildCard('Keuntungan Pulsa', _formatRupiah(_data['keuntunganPulsaHariIni']), Icons.trending_up, Colors.indigo.shade600),
                                  _buildCard('Total Pendapatan', _formatRupiah(_data['totalPendapatanGabungan']), Icons.analytics, Colors.deepOrange.shade600),
                                ],
                              ),
                            ],
                          ),
                        ),
                        const CustomFooter(),
                      ],
                    ),
                  ),
                ),
    );
  }

  Widget _buildCard(String title, String value, IconData icon, Color color) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [BoxShadow(color: Colors.grey.shade200, blurRadius: 8, offset: const Offset(0, 2))],
      ),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(icon, size: 32, color: color),
          const SizedBox(height: 8),
          Text(value, style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: color), textAlign: TextAlign.center),
          const SizedBox(height: 4),
          Text(title, style: TextStyle(fontSize: 12, color: Colors.grey.shade600), textAlign: TextAlign.center),
        ],
      ),
    );
  }
}