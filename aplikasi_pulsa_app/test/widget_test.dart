import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:aplikasi_pulsa_app/main.dart' as app;

void main() {
  group('Aplikasi Pulsa IO Tests', () {
    testWidgets('Aplikasi berjalan dan menampilkan halaman login', (WidgetTester tester) async {
      // Build aplikasi
      await tester.pumpWidget(const app.MyApp());
      
      // Tunggu sebentar
      await tester.pumpAndSettle();
      
      // Verifikasi bahwa halaman login ditampilkan
      // Cari elemen yang ada di halaman login
      expect(find.text('Pulsa IO - Test API'), findsOneWidget);
      expect(find.text('LOGIN'), findsOneWidget);
      expect(find.text('Username'), findsOneWidget);
      expect(find.text('Password'), findsOneWidget);
    });

    testWidgets('Field username dan password bisa diisi', (WidgetTester tester) async {
      // Build aplikasi
      await tester.pumpWidget(const app.MyApp());
      await tester.pumpAndSettle();
      
      // Cari field username dan isi
      final usernameField = find.widgetWithText(TextField, 'Username');
      expect(usernameField, findsOneWidget);
      
      await tester.enterText(usernameField, 'admin');
      
      // Cari field password dan isi
      final passwordField = find.widgetWithText(TextField, 'Password');
      expect(passwordField, findsOneWidget);
      
      await tester.enterText(passwordField, 'admin123');
      
      // Verifikasi teks sudah masuk
      expect(find.text('admin'), findsOneWidget);
    });

    testWidgets('Tombol login tersedia dan aktif', (WidgetTester tester) async {
      // Build aplikasi
      await tester.pumpWidget(const app.MyApp());
      await tester.pumpAndSettle();
      
      // Cari tombol login
      final loginButton = find.widgetWithText(ElevatedButton, 'LOGIN');
      expect(loginButton, findsOneWidget);
      
      // Verifikasi tombol enabled (tidak disable)
      final button = tester.widget<ElevatedButton>(loginButton);
      expect(button.enabled, true);
    });

    testWidgets('Menampilkan status "Belum Login" saat awal', (WidgetTester tester) async {
      // Build aplikasi
      await tester.pumpWidget(const app.MyApp());
      await tester.pumpAndSettle();
      
      // Verifikasi teks status
      expect(find.text('Belum Login'), findsOneWidget);
      expect(find.text('Sudah Login'), findsNothing);
    });
  });
}