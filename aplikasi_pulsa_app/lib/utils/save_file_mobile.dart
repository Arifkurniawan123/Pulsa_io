import 'dart:io';
import 'package:path_provider/path_provider.dart';

Future<String> saveFileBytesImpl(String filename, List<int> bytes) async {
  Directory directory;

  if (Platform.isAndroid) {
    // Path langsung ke folder Downloads (umum di semua HP Android)
    directory = Directory('/storage/emulated/0/Download');
    
    // Buat folder jika belum ada
    if (!await directory.exists()) {
      await directory.create(recursive: true);
    }
  } else {
    // iOS atau lainnya: pakai document directory
    directory = await getApplicationDocumentsDirectory();
  }

  final file = File('${directory.path}/$filename');
  await file.writeAsBytes(bytes);
  return file.path;
}