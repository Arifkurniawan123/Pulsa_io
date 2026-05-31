import 'package:flutter/material.dart';

class ScannerView extends StatelessWidget {
  final ValueChanged<String> onDetect;

  const ScannerView({super.key, required this.onDetect});

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 180,
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.grey.shade400),
      ),
      padding: const EdgeInsets.all(16),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
            children: [
          Icon(Icons.qr_code_scanner, size: 48, color: Colors.grey),
          SizedBox(height: 16),
              Text(
            'Pemindai kamera tidak tersedia di web.\nSilakan masukkan data secara manual.',
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }
}
