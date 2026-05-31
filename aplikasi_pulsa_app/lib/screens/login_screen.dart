import 'package:flutter/material.dart';
import 'package:dio/dio.dart';
import '../services/api_service.dart';
import 'dashboard_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final TextEditingController _usernameController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();
  final ApiService _api = ApiService();
  
  bool _isLoading = false;
  String _error = '';

  @override
  void dispose() {
    _usernameController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _login() async {
    final username = _usernameController.text.trim();
    final password = _passwordController.text.trim();

    if (username.isEmpty || password.isEmpty) {
      setState(() => _error = 'Username dan password wajib diisi');
      return;
    }

    setState(() {
      _isLoading = true;
      _error = '';
    });

    try {
      final response = await _api.login(username, password);
      
      if (response.statusCode == 200 && response.data['success'] == true) {
        final token = response.data['data']['token'];
        final user = Map<String, dynamic>.from(response.data['data']['user'] ?? {});

        await _api.saveToken(token);
        await _api.saveUserInfo(user);

        final roleId = user['role_id'] is int ? user['role_id'] : int.tryParse(user['role_id']?.toString() ?? '') ?? 0;
        final routeName = roleId == 2 ? '/dashboard' : '/kasir';

        if (mounted) {
          Navigator.pushReplacementNamed(context, routeName);
        }
      } else {
        setState(() => _error = response.data['message'] ?? 'Login gagal');
      }
    } on DioException catch (e) {
      setState(() {
        _error = e.response?.data['message'] ?? 'Koneksi gagal. Pastikan server menyala.';
      });
    } catch (e) {
      setState(() => _error = 'Terjadi kesalahan: $e');
    } finally {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      body: Center(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              // Logo
              Container(
                width: 80,
                height: 80,
                decoration: BoxDecoration(
                  color: Colors.grey.shade100,
                  shape: BoxShape.circle,
                  boxShadow: [
                    BoxShadow(
                      color: Colors.indigo.shade100,
                      blurRadius: 20,
                      spreadRadius: 5,
                    ),
                  ],
                ),
                child: const Icon(
                  Icons.signal_cellular_alt,
                  size: 45,
                  color: Color(0xFF6366f1),
                ),
              ),
              const SizedBox(height: 16),
              const Text(
                'Puput Cell',
                style: TextStyle(
                  fontSize: 28,
                  fontWeight: FontWeight.bold,
                  color: Color(0xFF4c4c6d),
                ),
              ),
              const SizedBox(height: 8),
              Text(
                'Masuk untuk mengelola transaksi dan data',
                style: TextStyle(
                  fontSize: 14,
                  color: Colors.grey.shade600,
                ),
              ),
              const SizedBox(height: 40),
              // Form
              Container(
                constraints: const BoxConstraints(maxWidth: 400),
                child: Column(
                  children: [
                    TextField(
                      controller: _usernameController,
                      decoration: InputDecoration(
                        labelText: 'Username',
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(10),
                        ),
                        prefixIcon: const Icon(Icons.person, color: Color(0xFF6366f1)),
                        filled: true,
                        fillColor: Colors.grey.shade50,
                      ),
                    ),
                    const SizedBox(height: 16),
                    TextField(
                      controller: _passwordController,
                      obscureText: true,
                      decoration: InputDecoration(
                        labelText: 'Password',
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(10),
                        ),
                        prefixIcon: const Icon(Icons.lock, color: Color(0xFF6366f1)),
                        filled: true,
                        fillColor: Colors.grey.shade50,
                      ),
                    ),
                    if (_error.isNotEmpty) ...[
                      const SizedBox(height: 16),
                      Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: Colors.red.shade50,
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Row(
                          children: [
                            const Icon(Icons.error_outline, color: Colors.red, size: 20),
                            const SizedBox(width: 8),
                            Expanded(
                              child: Text(
                                _error,
                                style: const TextStyle(color: Colors.red, fontSize: 13),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                    const SizedBox(height: 24),
                    SizedBox(
                      width: double.infinity,
                      height: 48,
                      child: ElevatedButton(
                        onPressed: _isLoading ? null : _login,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF6366f1),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(10),
                          ),
                        ),
                        child: _isLoading
                            ? const CircularProgressIndicator(color: Colors.white)
                            : const Text(
                                'LOGIN',
                                style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600),
                              ),
                      ),
                    ),
                    const SizedBox(height: 24),
                    Text(
                      '© 2025 Pulsa IO',
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey.shade400,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}