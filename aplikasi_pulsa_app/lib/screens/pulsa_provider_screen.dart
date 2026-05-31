import 'package:flutter/material.dart';
import 'package:dio/dio.dart';
import '../services/api_service.dart';
import '../models/provider_pulsa.dart';
import '../widgets/custom_sidebar.dart';
import 'pulsa_product_screen.dart';

class PulsaProviderScreen extends StatefulWidget {
  const PulsaProviderScreen({super.key});

  @override
  State<PulsaProviderScreen> createState() => _PulsaProviderScreenState();
}

class _PulsaProviderScreenState extends State<PulsaProviderScreen> {
  final ApiService _api = ApiService();
  List<ProviderPulsa> _providers = [];
  bool _isLoading = true;
  String _error = '';
  int _userRole = 0;

  @override
  void initState() {
    super.initState();
    _loadUserRole();
    _fetchProviders();
  }

  Future<void> _loadUserRole() async {
    final roleId = await _api.getUserRole();
    if (!mounted) return;
    setState(() {
      _userRole = roleId ?? 0;
    });
  }

  Future<void> _fetchProviders() async {
    if (!mounted) return;
    setState(() {
      _isLoading = true;
      _error = '';
    });
    try {
      final response = await _api.getProviders();
      if (response.statusCode == 200 && response.data['success'] == true) {
        final List raw = response.data['data'] as List;
        setState(() {
          _providers = raw.map((e) => ProviderPulsa.fromJson(e)).toList();
          _isLoading = false;
        });
      } else {
        setState(() {
          _error = response.data['message'] ?? 'Gagal mengambil data provider';
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

  void _onSidebarItemSelected(int index) async {
    final roleId = await _api.getUserRole();
    switch (index) {
      case 0:
        if (roleId == 2) Navigator.pushReplacementNamed(context, '/dashboard');
        else Navigator.pushReplacementNamed(context, '/kasir');
        break;
      case 1:
        // sudah di halaman ini
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
        } else {
          Navigator.pushReplacementNamed(context, '/history');
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
    const selectedIndex = 1;

    return Scaffold(
      drawer: CustomSidebar(selectedIndex: selectedIndex, onItemSelected: _onSidebarItemSelected),
      appBar: AppBar(title: const Text('Pilih Provider Pulsa')),
      body: RefreshIndicator(
        onRefresh: _fetchProviders,
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
                          onPressed: _fetchProviders,
                          child: const Text('Coba Lagi'),
                        ),
                      ],
                    ),
                  )
                : _providers.isEmpty
                    ? const Center(child: Text('Tidak ada provider tersedia'))
                    : ListView.builder(
                        padding: const EdgeInsets.all(16),
                        itemCount: _providers.length,
                        itemBuilder: (context, index) {
                          final p = _providers[index];
                          return Card(
                            margin: const EdgeInsets.only(bottom: 12),
                            child: ListTile(
                              leading: const Icon(Icons.sim_card),
                              title: Text(p.namaProvider),
                              subtitle: Text(p.kodeProvider),
                              trailing: const Icon(Icons.chevron_right),
                              onTap: () {
                                Navigator.push(
                                  context,
                                  MaterialPageRoute(
                                    builder: (_) => PulsaProductScreen(
                                      providerId: p.id,
                                      namaProvider: p.namaProvider,
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