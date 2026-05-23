class DigiflazzProduct {
  final String productName;
  final String category;
  final String brand;
  final double price;
  final String buyerSkuCode;
  final bool buyerProductStatus;
  final String desc;

  DigiflazzProduct({
    required this.productName,
    required this.category,
    required this.brand,
    required this.price,
    required this.buyerSkuCode,
    required this.buyerProductStatus,
    required this.desc,
  });

  factory DigiflazzProduct.fromJson(Map<String, dynamic> json) {
    return DigiflazzProduct(
      productName: json['product_name'] ?? '',
      category: json['category'] ?? '',
      brand: json['brand'] ?? '',
      price: (json['price'] ?? 0).toDouble(),
      buyerSkuCode: json['buyer_sku_code'] ?? '',
      buyerProductStatus: json['buyer_product_status'] ?? false,
      desc: json['desc'] ?? '',
    );
  }
}