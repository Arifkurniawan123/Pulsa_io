import 'package:dio/dio.dart';
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  static final ApiService _instance = ApiService._internal();
  factory ApiService() => _instance;
  ApiService._internal() {
    _setupDio();
  }

  static const String baseUrl = 'http://localhost:8080/';
  late final Dio _dio;

  // Cache untuk pricelist
  Map<String, dynamic>? _cachedPricelist;
  DateTime _cachedTime = DateTime.now().subtract(const Duration(hours: 1));
  static const _cacheDuration = Duration(hours: 1);

  void _setupDio() {
    _dio = Dio(BaseOptions(
      baseUrl: baseUrl,
      connectTimeout: const Duration(seconds: 30),
      receiveTimeout: const Duration(seconds: 30),
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'Accept': 'application/json',
      },
    ));

    _dio.interceptors.add(InterceptorsWrapper(
      onRequest: (options, handler) async {
        final prefs = await SharedPreferences.getInstance();
        final token = prefs.getString('jwt_token');
        if (token != null && token.isNotEmpty) {
          options.headers['Authorization'] = 'Bearer $token';
        }
        return handler.next(options);
      },
    ));
  }

  Future<Response> getDigiflazzPricelist({bool forceRefresh = false}) async {
    // Gunakan cache jika masih valid dan tidak dipaksa refresh
    if (!forceRefresh && _cachedPricelist != null && DateTime.now().difference(_cachedTime) < _cacheDuration) {
      print('📦 Menggunakan cache pricelist (terakhir diambil $_cachedTime)');
      return Response(
        requestOptions: RequestOptions(path: '/api/digiflazz/pricelist'),
        data: _cachedPricelist,
        statusCode: 200,
      );
    }

    print('🌐 Mengambil pricelist baru dari DigiFlazz');
    final response = await _dio.get('/api/digiflazz/pricelist');

    // Simpan cache hanya jika sukses dan tidak ada error limitasi (rc 83)
    if (response.statusCode == 200 && response.data != null && response.data is Map) {
      final innerData = response.data['data'];
      if (innerData is Map && innerData['rc'] == '83') {
        print('⚠️ Limitasi DigiFlazz, tidak menyimpan cache');
      } else {
        _cachedPricelist = response.data;
        _cachedTime = DateTime.now();
      }
    }
    return response;
  }

  Future<Response> topupPulsa(Map<String, dynamic> data) async {
    final formData = FormData.fromMap(data);
    return await _dio.post('/api/digiflazz/topup', data: formData);
  }

  Future<Response> login(String username, String password) async {
    final formData = FormData.fromMap({'username': username, 'password': password});
    return await _dio.post('/api/login', data: formData);
  }

  Future<Response> logout() async {
    return await _dio.post('/api/logout');
  }

  Future<void> saveToken(String token) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('jwt_token', token);
  }

  Future<void> clearToken() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('jwt_token');
  }

  Future<bool> isLoggedIn() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('jwt_token');
    return token != null && token.isNotEmpty;
  }

  Future<Response> getDashboard() async {
    return await _dio.get('/api/dashboard');
  }
}