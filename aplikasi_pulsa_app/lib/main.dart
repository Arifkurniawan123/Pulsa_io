import 'package:flutter/material.dart';
import 'screens/login_screen.dart';
import 'screens/dashboard_screen.dart';
import 'screens/kasir_screen.dart';
import 'screens/pulsa_provider_screen.dart';
import 'screens/topup_saldo_screen.dart';
import 'screens/user_screen.dart';
import 'screens/history_screen.dart';import 'screens/scan_pulsa_screen.dart';

void main() {
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Pulsa IO',
      theme: ThemeData(primarySwatch: Colors.indigo, useMaterial3: true),
      initialRoute: '/',
      routes: {
        '/': (context) => const LoginScreen(),
        '/dashboard': (context) => const DashboardScreen(),
        '/pulsa-provider': (context) => const PulsaProviderScreen(),
        '/topup-saldo': (context) => const TopupSaldoScreen(),
        '/scan-pulsa': (context) => const ScanPulsaScreen(),
        '/user': (context) => const UserScreen(),
        '/history': (context) => const HistoryScreen(),
      },
      debugShowCheckedModeBanner: false,
    );
  }
}