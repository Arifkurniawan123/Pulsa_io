import 'dart:io';
import 'package:dio/dio.dart';
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  static final ApiService _instance = ApiService._internal();
  factory ApiService() => _instance;
  ApiService._internal() {
    _setupDio();
  }

  static const String baseUrl = 'http://localhost:8080/';
  static const String _tokenKey = 'jwt_token';
  static const String _userRoleKey = 'user_role';
  static const String _userNameKey = 'user_name';
  static const String _usernameKey = 'username';

  late final Dio _dio;

  void _setupDio() {
    _dio = Dio(BaseOptions(
      baseUrl: baseUrl,
      connectTimeout: const Duration(seconds: 30),
      receiveTimeout: const Duration(seconds: 30),
      headers: {'Accept': 'application/json'},
    ));

    _dio.interceptors.add(InterceptorsWrapper(
      onRequest: (options, handler) async {
        final prefs = await SharedPreferences.getInstance();
        final token = prefs.getString(_tokenKey);
        if (token != null && token.isNotEmpty) {
          options.headers['Authorization'] = 'Bearer $token';
        }
        return handler.next(options);
      },
      onError: (error, handler) async {
        if (error.response?.statusCode == 401) {
          await clearToken();
        }
        return handler.next(error);
      },
    ));
  }

  // ==================== AUTH ====================
  Future<Response> login(String username, String password) async {
    return await _dio.post(
      '/api/login',
      data: FormData.fromMap({'username': username, 'password': password}),
    );
  }

  Future<Response> logout() async => await _dio.post('/api/logout');

  Future<void> saveToken(String token) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_tokenKey, token);
  }

  Future<void> saveUserInfo(Map<String, dynamic> user) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setInt(
      _userRoleKey,
      user['role_id'] is int ? user['role_id'] : int.tryParse(user['role_id']?.toString() ?? '') ?? 0,
    );
    await prefs.setString(_userNameKey, user['name']?.toString() ?? '');
    await prefs.setString(_usernameKey, user['username']?.toString() ?? '');
  }

  Future<void> clearToken() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_tokenKey);
    await prefs.remove(_userRoleKey);
    await prefs.remove(_userNameKey);
    await prefs.remove(_usernameKey);
  }

  Future<int?> getUserRole() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getInt(_userRoleKey);
  }

  Future<String?> getUserName() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(_userNameKey);
  }

  Future<String?> getUsername() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(_usernameKey);
  }

  Future<bool> isLoggedIn() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString(_tokenKey);
    return token != null && token.isNotEmpty;
  }

  // ==================== DASHBOARD ====================
  Future<Response> getDashboard() async => await _dio.get('/api/dashboard');

  // ==================== PROVIDER & NOMINAL ====================
  Future<Response> getProviders() async {
    return await _dio.get('/api/provider/allowed');
  }

  Future<Response> getNominalByProvider(int providerId) async {
    return await _dio.get('/api/nominal/provider/$providerId');
  }

  // ==================== TOPUP PULSA (DIGIFLAZZ) ====================
  Future<Response> topupPulsa(Map<String, dynamic> data) async {
    return await _dio.post('/api/digiflazz/topup', data: FormData.fromMap(data));
  }

  // ==================== TOPUP SALDO ====================
  Future<Response> getSaldoDigiflazz() async {
    return await _dio.get('/api/topup-saldo/saldo');
  }

  Future<Response> getTopupHistory({String tipe = 'topup_saldo', int limit = 50}) async {
    final queryParameters = <String, dynamic>{'limit': limit};
    if (tipe.isNotEmpty) queryParameters['tipe'] = tipe;
    return await _dio.get('/api/topup-saldo/history', queryParameters: queryParameters);
  }

  Future<Response> simulasiTopupSaldo(int nominal, String metodePembayaran) async {
    return await _dio.post(
      '/api/topup-saldo/simulasi',
      data: FormData.fromMap({'nominal': nominal, 'metode_pembayaran': metodePembayaran}),
    );
  }

  // ==================== TOPUP E-WALLET ====================
  Future<Response> topupEwalletInitiate(String metodeEwallet, String nomorTelepon, int nominal) async {
    return await _dio.post(
      '/api/topup-ewallet',
      data: FormData.fromMap({
        'metode_ewallet': metodeEwallet,
        'nomor_telepon': nomorTelepon,
        'nominal': nominal,
      }),
    );
  }

  Future<Response> topupEwalletConfirm(String refId) async {
    return await _dio.post('/api/topup-ewallet/confirm/$refId');
  }

  Future<Response> getEwalletHistory({int limit = 50, int offset = 0}) async {
    return await _dio.get('/api/topup-ewallet/history', queryParameters: {'limit': limit, 'offset': offset});
  }

  Future<Response> getSupportedEwallets() async {
    return await _dio.get('/api/topup-ewallet/supported-methods');
  }

  // ==================== TOPUP BANK ====================
  Future<Response> topupBankInitiate(String namaBank, String nomorRekening, String atasNama, int nominal) async {
    return await _dio.post(
      '/api/topup-bank',
      data: FormData.fromMap({
        'nama_bank': namaBank,
        'nomor_rekening': nomorRekening,
        'atas_nama': atasNama,
        'nominal': nominal,
      }),
    );
  }

  Future<Response> topupBankConfirm(String refId) async {
    return await _dio.post('/api/topup-bank/confirm/$refId');
  }

  Future<Response> getBankHistory({int limit = 50, int offset = 0}) async {
    return await _dio.get('/api/topup-bank/history', queryParameters: {'limit': limit, 'offset': offset});
  }

  Future<Response> getSupportedBanks() async {
    return await _dio.get('/api/topup-bank/supported-banks');
  }

  // ==================== USER CRUD ====================
  Future<Response> getUsers() async => await _dio.get('/api/user');

  Future<Response> createUser({
    required String namaLengkap,
    required int role,
    required String username,
    required String password,
    required String email,
    required String noTelp,
    required String alamat,
    File? imageFile,
  }) async {
    final map = <String, dynamic>{
      'nama_lengkap': namaLengkap,
      'role': role,
      'username': username,
      'password': password,
      'email': email,
      'no_tlp': noTelp,
      'alamat': alamat,
    };
    if (imageFile != null) {
      map['image'] = await MultipartFile.fromFile(imageFile.path, filename: imageFile.path.split('/').last);
    }
    return await _dio.post('/api/user/store', data: FormData.fromMap(map));
  }

  Future<Response> updateUser({
    required String id,
    required String namaLengkap,
    required String username,
    required String password,
    required String email,
    required String noTelp,
    required String alamat,
    required int role,
    File? imageFile,
  }) async {
    final map = <String, dynamic>{
      'nama_lengkap': namaLengkap,
      'username': username,
      'email': email,
      'no_tlp': noTelp,
      'alamat': alamat,
      'role': role,
    };
    if (password.isNotEmpty) map['password'] = password;
    if (imageFile != null) {
      map['image'] = await MultipartFile.fromFile(imageFile.path, filename: imageFile.path.split('/').last);
    }
    return await _dio.post('/api/user/update/$id', data: FormData.fromMap(map));
  }

  Future<Response> deleteUser(String id) async {
    return await _dio.post('/api/user/delete/$id');
  }

  // ==================== KASIR ====================
  Future<Response> getKasirData() async => await _dio.get('/api/kasir');

  Future<Response> addKasirFisik(String produkId, int jumlah) async {
    return await _dio.post(
      '/api/kasir/add',
      data: FormData.fromMap({'jenis_produk': 'fisik', 'produk_id': produkId, 'jumlah': jumlah}),
    );
  }

  Future<Response> addKasirDigital(String noTujuan, int providerId, int nominalId, String metodePembayaran) async {
    return await _dio.post(
      '/api/kasir/add',
      data: FormData.fromMap({
        'jenis_produk': 'digital',
        'no_tujuan_pulsa': noTujuan,
        'provider_id': providerId,
        'nominal_id': nominalId,
        'metode_pembayaran_pulsa': metodePembayaran,
      }),
    );
  }

  Future<Response> removeKasirItem(String itemId) async {
    return await _dio.post('/api/kasir/remove', data: FormData.fromMap({'item_id': itemId}));
  }

  Future<Response> checkoutKasir({
    required String metodePembayaran,
    required double ppnPercent,
    required double ppn,
    required double diskon,
    required double grandTotal,
  }) async {
    return await _dio.post(
      '/api/kasir/checkout',
      data: FormData.fromMap({
        'metode_pembayaran': metodePembayaran,
        'ppn_percent': ppnPercent,
        'ppn': ppn,
        'diskon': diskon,
        'grand_total': grandTotal,
      }),
    );
  }

  // ==================== LAPORAN PULSA (CRITICAL) ====================
  Future<Response> getLaporanPulsa({String? startDate, String? endDate, String? status}) async {
    final params = <String, dynamic>{};
    if (startDate != null) params['start_date'] = startDate;
    if (endDate != null) params['end_date'] = endDate;
    if (status != null) params['status'] = status;
    return await _dio.get('/api/laporan-pulsa', queryParameters: params);
  }

  Future<Response> createLaporanPulsa({
    required String noTujuan,
    required int providerId,
    required int nominalId,
    required String metodePembayaran,
  }) async {
    final data = FormData.fromMap({
      'no_tujuan': noTujuan,
      'provider_id': providerId,
      'nominal_id': nominalId,
      'metode_pembayaran': metodePembayaran,
    });
    return await _dio.post('/api/laporan-pulsa', data: data);
  }

  Future<Response> updateLaporanPulsa({
    required int id,
    required String noTujuan,
    required int providerId,
    required int nominalId,
    required String metodePembayaran,
    required String status,
  }) async {
    final data = FormData.fromMap({
      'no_tujuan': noTujuan,
      'provider_id': providerId,
      'nominal_id': nominalId,
      'metode_pembayaran': metodePembayaran,
      'status': status,
    });
    return await _dio.post('/api/laporan-pulsa/update/$id', data: data);
  }

  Future<Response> deleteLaporanPulsa(int id) async {
    return await _dio.post('/api/laporan-pulsa/delete/$id');
  }

  Future<Response> getLaporanProviders() async {
    return await _dio.get('/api/laporan-pulsa/providers');
  }

  Future<Response> getNominalsForProvider(int providerId) async {
    return await _dio.get('/api/laporan-pulsa/nominals/$providerId');
  }
}