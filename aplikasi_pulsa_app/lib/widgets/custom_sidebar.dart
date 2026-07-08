import 'package:flutter/material.dart';
import '../services/api_service.dart';

class CustomSidebar extends StatefulWidget {
  final String? currentRoute;

  const CustomSidebar({
    super.key,
    this.currentRoute,
  });

  @override
  State<CustomSidebar> createState() => _CustomSidebarState();
}

class _CustomSidebarState extends State<CustomSidebar> {
  late Future<Map<String, dynamic>> _userFuture;
  Set<String> _expandedGroups = {'dashboard'};

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

        return Drawer(
          child: Column(
            children: [
              _buildHeader(userName, isAdmin),
              Expanded(
                child: ListView(
                  padding: EdgeInsets.zero,
                  children: [
                    if (isAdmin) _buildMenuGroup('Dashboard', [
                      {'icon': Icons.dashboard, 'title': 'Dashboard', 'desc': 'Ringkasan bisnis hari ini', 'route': '/dashboard'},
                    ]),
                    _buildMenuGroup('Transaksi', [
                      {'icon': Icons.mobile_friendly, 'title': 'Pulsa Reguler', 'desc': 'Kelola paket pulsa', 'route': '/pulsa-provider'},
                      {'icon': Icons.account_balance_wallet, 'title': 'Top-up Saldo', 'desc': 'Isi saldo akun', 'route': '/topup-saldo'},
                      {'icon': Icons.receipt_long, 'title': 'Laporan Pulsa', 'desc': 'Catat & kelola laporan pulsa', 'route': '/laporan-pulsa'},
                    ]),
                    if (isAdmin) _buildMenuGroup('Manajemen', [
                      {'icon': Icons.person_add, 'title': 'Pengguna', 'desc': 'Kelola data pengguna', 'route': '/user'},
                    ]),
                    _buildMenuGroup('Laporan', [
                      {'icon': Icons.history, 'title': 'Riwayat', 'desc': 'Lihat riwayat transaksi', 'route': '/history'},
                    ]),
                  ],
                ),
              ),
              _buildLogoutButton(context),
            ],
          ),
        );
      },
    );
  }

  Widget _buildHeader(String userName, bool isAdmin) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(vertical: 32, horizontal: 20),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [
            const Color(0xFF6366f1),
            const Color(0xFF8B5CF6),
          ],
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.indigo.withOpacity(0.2),
            blurRadius: 12,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Colors.white.withOpacity(0.2),
              borderRadius: BorderRadius.circular(12),
            ),
            child: const Icon(Icons.store, size: 40, color: Colors.white),
          ),
          const SizedBox(height: 16),
          const Text(
            'Pulsa IO',
            style: TextStyle(
              fontSize: 24,
              fontWeight: FontWeight.w700,
              color: Colors.white,
              letterSpacing: 0.5,
            ),
          ),
          const SizedBox(height: 12),
          Container(
            height: 1,
            color: Colors.white.withOpacity(0.2),
          ),
          const SizedBox(height: 12),
          Text(
            userName.isNotEmpty ? userName : 'Selamat datang',
            style: const TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.w500,
              color: Colors.white,
            ),
          ),
          const SizedBox(height: 4),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
            decoration: BoxDecoration(
              color: Colors.white.withOpacity(0.25),
              borderRadius: BorderRadius.circular(6),
            ),
            child: Text(
              isAdmin ? '👤 Administrator' : '💼 Kasir',
              style: const TextStyle(
                fontSize: 11,
                fontWeight: FontWeight.w600,
                color: Colors.white,
                letterSpacing: 0.3,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildMenuGroup(String groupTitle, List<Map<String, dynamic>> items) {
    final isExpanded = _expandedGroups.contains(groupTitle);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
          child: Row(
            children: [
              Container(
                width: 3,
                height: 20,
                decoration: BoxDecoration(
                  color: const Color(0xFF6366f1),
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
              const SizedBox(width: 10),
              Text(
                groupTitle,
                style: const TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w700,
                  color: Color(0xFF6366f1),
                  letterSpacing: 0.5,
                ),
              ),
              const Spacer(),
              GestureDetector(
                onTap: () {
                  setState(() {
                    if (isExpanded) {
                      _expandedGroups.remove(groupTitle);
                    } else {
                      _expandedGroups.add(groupTitle);
                    }
                  });
                },
                child: Icon(
                  isExpanded ? Icons.expand_less : Icons.expand_more,
                  size: 18,
                  color: Colors.grey.shade400,
                ),
              ),
            ],
          ),
        ),
        if (isExpanded)
          Column(
            children: items.map((item) {
              return _buildMenuItem(
                item['icon'] as IconData,
                item['title'] as String,
                item['desc'] as String,
                context,
                route: item['route'] as String,
                isSelected: widget.currentRoute == item['route'],
              );
            }).toList(),
          ),
      ],
    );
  }

  Widget _buildMenuItem(
    IconData icon,
    String title,
    String description,
    BuildContext context, {
    required String route,
    bool isSelected = false,
  }) {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
      decoration: BoxDecoration(
        color: isSelected ? Colors.indigo.shade50 : Colors.transparent,
        borderRadius: BorderRadius.circular(8),
        border: isSelected ? Border.all(color: const Color(0xFF6366f1), width: 1.5) : null,
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: !isSelected
              ? () => Navigator.pushReplacementNamed(context, route)
              : null,
          borderRadius: BorderRadius.circular(8),
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: isSelected
                        ? const Color(0xFF6366f1).withOpacity(0.1)
                        : Colors.grey.shade100,
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Icon(
                    icon,
                    size: 18,
                    color: isSelected
                        ? const Color(0xFF6366f1)
                        : Colors.grey.shade600,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        title,
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: isSelected ? FontWeight.w600 : FontWeight.w500,
                          color: isSelected
                              ? const Color(0xFF6366f1)
                              : Colors.black87,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        description,
                        style: TextStyle(
                          fontSize: 11,
                          color: Colors.grey.shade500,
                          height: 1.2,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                  ),
                ),
                if (isSelected)
                  Container(
                    width: 4,
                    height: 24,
                    decoration: BoxDecoration(
                      color: const Color(0xFF6366f1),
                      borderRadius: BorderRadius.circular(2),
                    ),
                  ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildLogoutButton(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        border: Border(
          top: BorderSide(color: Colors.grey.shade200),
        ),
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: () => _logout(context),
          borderRadius: BorderRadius.circular(8),
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
            decoration: BoxDecoration(
              color: Colors.red.shade50,
              borderRadius: BorderRadius.circular(8),
              border: Border.all(color: Colors.red.shade200, width: 1),
            ),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: Colors.red.shade100,
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Icon(
                    Icons.logout,
                    size: 18,
                    color: Colors.red.shade600,
                  ),
                ),
                const SizedBox(width: 12),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Logout',
                      style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w600,
                        color: Colors.red.shade600,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      'Keluar dari akun',
                      style: TextStyle(
                        fontSize: 11,
                        color: Colors.red.shade400,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}