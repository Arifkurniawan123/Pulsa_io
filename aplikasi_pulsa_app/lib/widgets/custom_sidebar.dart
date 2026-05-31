import 'package:flutter/material.dart';
import '../services/api_service.dart';

class CustomSidebar extends StatefulWidget {
  final int selectedIndex;
  final Function(int) onItemSelected;

  const CustomSidebar({
    super.key,
    required this.selectedIndex,
    required this.onItemSelected,
  });

  @override
  State<CustomSidebar> createState() => _CustomSidebarState();
}

class _CustomSidebarState extends State<CustomSidebar> {
  late Future<Map<String, dynamic>> _userFuture;

  @override
  void initState() {
    super.initState();
    _userFuture = _loadUserData();
  }

  Future<Map<String, dynamic>> _loadUserData() async {
    final api = ApiService();
    final roleId = await api.getUserRole() ?? 0;
    final userName = await api.getUserName() ?? '';
    return {
      'role_id': roleId,
      'user_name': userName,
    };
  }

  Future<void> _logout(BuildContext context) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Logout'),
        content: const Text('Apakah Anda yakin ingin logout?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Batal')),
          TextButton(onPressed: () => Navigator.pop(context, true), child: const Text('Logout', style: TextStyle(color: Colors.red))),
        ],
      ),
    );
    if (confirm != true) return;

    final api = ApiService();
    await api.clearToken();
    if (context.mounted) {
      Navigator.of(context).pushNamedAndRemoveUntil('/', (route) => false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return FutureBuilder<Map<String, dynamic>>(
      future: _userFuture,
      builder: (context, snapshot) {
        final roleId = snapshot.data?['role_id'] ?? 0;
        final userName = snapshot.data?['user_name'] ?? '';
        final isAdmin = roleId == 2;
        final roleLabel = isAdmin ? 'Administrator' : 'Kasir';
        final menuItems = [
          {'icon': Icons.dashboard, 'title': isAdmin ? 'Dashboard' : ''},
          {'icon': Icons.mobile_friendly, 'title': 'Pulsa Reguler'},
          {'icon': Icons.account_balance_wallet, 'title': 'Top-up Saldo'},
          {'icon': Icons.qr_code_scanner, 'title': 'Scan Pulsa'},
          if (isAdmin) {'icon': Icons.person_add, 'title': 'Pengguna'},
          {'icon': Icons.history, 'title': 'Riwayat'},
        ];

        return Drawer(
          child: Column(
            children: [
              Container(
                width: double.infinity,
                padding: const EdgeInsets.symmetric(vertical: 40, horizontal: 20),
                decoration: const BoxDecoration(
                  gradient: LinearGradient(colors: [Color(0xFF6366f1), Color(0xFF8B5CF6)]),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Icon(Icons.store, size: 50, color: Colors.white),
                    const SizedBox(height: 10),
                    const Text('Pulsa IO', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Colors.white)),
                    const SizedBox(height: 6),
                    Text(userName.isNotEmpty ? userName : 'Selamat datang', style: const TextStyle(fontSize: 14, color: Colors.white70)),
                    const SizedBox(height: 4),
                    Text(roleLabel, style: const TextStyle(fontSize: 12, color: Colors.white70)),
                  ],
                ),
              ),
              Expanded(
                child: ListView.builder(
                  itemCount: menuItems.length + 1,
                  itemBuilder: (context, index) {
                    if (index == menuItems.length) {
                      return Column(
                        children: [
                          const Divider(),
                          _buildMenuItem(Icons.logout, 'Logout', index, context, isLogout: true),
                        ],
                      );
                    }
                    final item = menuItems[index];
                    return _buildMenuItem(item['icon'] as IconData, item['title'] as String, index, context);
                  },
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildMenuItem(IconData icon, String title, int index, BuildContext context, {bool isLogout = false}) {
    final isSelected = widget.selectedIndex == index;
    return ListTile(
      leading: Icon(icon, color: isLogout ? Colors.red : (isSelected ? const Color(0xFF6366f1) : Colors.grey.shade600)),
      title: Text(
        title,
        style: TextStyle(
          color: isLogout ? Colors.red : (isSelected ? const Color(0xFF6366f1) : Colors.black87),
          fontWeight: isSelected ? FontWeight.w600 : null,
        ),
      ),
      tileColor: isSelected ? Colors.indigo.shade50 : null,
      onTap: () {
        if (isLogout) {
          _logout(context);
        } else {
          widget.onItemSelected(index);
        }
      },
    );
  }
}