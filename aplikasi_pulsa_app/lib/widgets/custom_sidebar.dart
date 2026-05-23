import 'package:flutter/material.dart';
import '../services/api_service.dart';

class CustomSidebar extends StatelessWidget {
  final int selectedIndex;
  final Function(int) onItemSelected;

  const CustomSidebar({
    super.key,
    required this.selectedIndex,
    required this.onItemSelected,
  });

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
              children: const [
                Icon(Icons.store, size: 50, color: Colors.white),
                SizedBox(height: 10),
                Text('Pulsa IO', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Colors.white)),
                Text('Admin Panel', style: TextStyle(fontSize: 12, color: Colors.white70)),
              ],
            ),
          ),
          Expanded(
            child: ListView(
              children: [
                _buildMenuItem(Icons.dashboard, 'Dashboard', 0, context),
                _buildMenuItem(Icons.mobile_friendly, 'Pulsa Reguler', 1, context),
                _buildMenuItem(Icons.history, 'Riwayat', 2, context),
                _buildMenuItem(Icons.people, 'User', 3, context),
                const Divider(),
                _buildMenuItem(Icons.logout, 'Logout', 4, context, isLogout: true),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildMenuItem(IconData icon, String title, int index, BuildContext context, {bool isLogout = false}) {
    final isSelected = selectedIndex == index;
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
          onItemSelected(index);
        }
      },
    );
  }
}