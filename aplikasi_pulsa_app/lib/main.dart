import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'providers/pricelist_provider.dart';
import 'screens/login_screen.dart';
import 'screens/dashboard_screen.dart';
import 'screens/pulsa_provider_screen.dart';

void main() {
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => PricelistProvider()),
      ],
      child: MaterialApp(
        title: 'Pulsa IO',
        theme: ThemeData(primarySwatch: Colors.indigo, useMaterial3: true),
        initialRoute: '/',
        routes: {
          '/': (context) => const LoginScreen(),
          '/dashboard': (context) => const DashboardScreen(),
          '/pulsa-provider': (context) => const PulsaProviderScreen(),
        },
        debugShowCheckedModeBanner: false,
      ),
    );
  }
}