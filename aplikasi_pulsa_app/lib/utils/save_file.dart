import 'save_file_stub.dart'
    if (dart.library.io) 'save_file_mobile.dart'
    if (dart.library.html) 'save_file_web.dart';

Future<String> saveFileBytes(String filename, List<int> bytes) => saveFileBytesImpl(filename, bytes);
