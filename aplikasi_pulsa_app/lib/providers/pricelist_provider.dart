import 'package:flutter/material.dart';
import 'package:dio/dio.dart';
import '../services/api_service.dart';
import '../models/digiflazz_product.dart';

class PricelistProvider extends ChangeNotifier {
  final ApiService _api = ApiService();
  List<DigiflazzProduct> _allProducts = [];
  bool _isLoading = false;
  String _error = '';

  List<DigiflazzProduct> get allProducts => _allProducts;
  bool get isLoading => _isLoading;
  String get error => _error;

  Future<void> fetchPricelist({bool forceRefresh = false}) async {
    if (_allProducts.isNotEmpty && !forceRefresh) return;

    _isLoading = true;
    _error = '';
    notifyListeners();

    try {
      final response = await _api.getDigiflazzPricelist();
      if (response.statusCode == 200 && response.data != null) {
        List rawData = [];
        if (response.data is Map && response.data.containsKey('data')) {
          rawData = response.data['data'] as List;
        } else if (response.data is List) {
          rawData = response.data;
        } else {
          throw Exception('Format response tidak dikenal');
        }
        _allProducts = rawData.map((e) => DigiflazzProduct.fromJson(e)).toList();
        _error = '';
      } else {
        _error = 'Gagal mengambil data produk';
      }
    } on DioException catch (e) {
      _error = 'Koneksi gagal: ${e.message}';
    } catch (e) {
      _error = 'Terjadi kesalahan: $e';
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  List<String> getBrandsByCategory(String category) {
    final products = _allProducts.where((p) => p.category == category && p.buyerProductStatus).toList();
    return products.map((p) => p.brand).toSet().toList();
  }

  List<DigiflazzProduct> getProductsByBrandAndCategory(String brand, String category) {
    return _allProducts.where((p) =>
        p.brand == brand &&
        p.category == category &&
        p.buyerProductStatus).toList();
  }
}