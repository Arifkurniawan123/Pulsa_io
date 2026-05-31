import 'dart:io';
import 'package:path_provider/path_provider.dart';

Future<String> saveFileBytesImpl(String filename, List<int> bytes) async {
  final directory = await getApplicationDocumentsDirectory();
  final file = File('${directory.path}/$filename');
  await file.writeAsBytes(bytes);
  return file.path;
}
