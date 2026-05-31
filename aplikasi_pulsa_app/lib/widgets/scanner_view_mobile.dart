import 'package:flutter/material.dart';
import 'package:mobile_scanner/mobile_scanner.dart';

class ScannerView extends StatelessWidget {
  final ValueChanged<String> onDetect;
  const ScannerView({super.key, required this.onDetect});

  @override
  Widget build(BuildContext context) {
    return ClipRRect(
      borderRadius: BorderRadius.circular(12),
      child: SizedBox(
        height: 280,
        child: MobileScanner(
          onDetect: (capture) {
            final barcode = capture.barcodes.first.rawValue;
            if (barcode != null && barcode.isNotEmpty) {
              onDetect(barcode);
            }
          },
        ),
      ),
    );
  }
}
