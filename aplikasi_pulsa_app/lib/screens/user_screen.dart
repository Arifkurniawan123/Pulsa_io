import 'dart:io';
import 'package:flutter/material.dart';
import 'package:dio/dio.dart';
import 'package:image_picker/image_picker.dart';
import '../services/api_service.dart';
import '../widgets/custom_sidebar.dart';

class UserScreen extends StatefulWidget {
  const UserScreen({super.key});

  @override
  State<UserScreen> createState() => _UserScreenState();
}

class _UserScreenState extends State<UserScreen> {
  final ApiService _api = ApiService();

  List<dynamic> _users = [];
  bool _isLoading = true;
  String _error = '';
  String _searchQuery = '';
  int _userRole = 0;

  @override
  void initState() {
    super.initState();
    _loadUserRole();
    _loadUsers();
  }

  Future<void> _loadUserRole() async {
    final roleId = await _api.getUserRole();
    if (!mounted) return;
    setState(() {
      _userRole = roleId ?? 0;
    });
  }

  Future<void> _loadUsers() async {
    setState(() {
      _isLoading = true;
      _error = '';
    });
    try {
      final response = await _api.getUsers();
      if (response.statusCode == 200 && response.data['success'] == true) {
        setState(() {
          _users = response.data['data'] ?? [];
          _isLoading = false;
        });
      } else {
        setState(() {
          _error = response.data['message'] ?? 'Gagal memuat data';
          _isLoading = false;
        });
      }
    } on DioException catch (e) {
      if (e.response?.statusCode == 401) {
        await _api.clearToken();
        if (mounted) Navigator.pushReplacementNamed(context, '/login');
      } else {
        setState(() {
          _error = e.response?.data['message'] ?? 'Koneksi gagal';
          _isLoading = false;
        });
      }
    } catch (e) {
      setState(() {
        _error = 'Terjadi kesalahan: $e';
        _isLoading = false;
      });
    }
  }

  List<dynamic> get _filteredUsers {
    if (_searchQuery.isEmpty) return _users;
    return _users.where((u) {
      final nama = (u['nama_lengkap'] ?? '').toLowerCase();
      final username = (u['username'] ?? '').toLowerCase();
      final role = (u['nama_role'] ?? '').toLowerCase();
      return nama.contains(_searchQuery.toLowerCase()) ||
          username.contains(_searchQuery.toLowerCase()) ||
          role.contains(_searchQuery.toLowerCase());
    }).toList();
  }

  void _showFormDialog({Map<String, dynamic>? user}) {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (_) => _UserFormDialog(
        api: _api,
        user: user,
        onSuccess: () {
          _loadUsers();
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(user == null
                  ? 'User berhasil ditambahkan'
                  : 'User berhasil diperbarui'),
              backgroundColor: Colors.green,
            ),
          );
        },
      ),
    );
  }

  Future<void> _deleteUser(String id, String nama) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Row(
          children: [
            Icon(Icons.warning, color: Colors.red),
            SizedBox(width: 8),
            Text('Hapus User'),
          ],
        ),
        content: Text('Apakah Anda yakin ingin menghapus user "$nama"?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            style: ElevatedButton.styleFrom(backgroundColor: Colors.red),
            child: const Text('Hapus', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );

    if (confirm != true) return;

    try {
      final response = await _api.deleteUser(id);
      if (response.statusCode == 200 && response.data['success'] == true) {
        _loadUsers();
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('User berhasil dihapus'),
              backgroundColor: Colors.red,
            ),
          );
        }
      } else {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text(response.data['message'] ?? 'Gagal hapus')),
          );
        }
      }
    } on DioException catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(e.response?.data['message'] ?? 'Koneksi gagal')),
        );
      }
    }
  }

  void _onSidebarItemSelected(int index) async {
    final roleId = await _api.getUserRole();
    switch (index) {
      case 0:
        if (roleId == 2) Navigator.pushReplacementNamed(context, '/dashboard');
        else Navigator.pushReplacementNamed(context, '/kasir');
        break;
      case 1:
        Navigator.pushReplacementNamed(context, '/pulsa-provider');
        break;
      case 2:
        Navigator.pushReplacementNamed(context, '/topup-saldo');
        break;
      case 3:
        Navigator.pushReplacementNamed(context, '/scan-pulsa');
        break;
      case 4:
        // sudah di user screen
        break;
      case 5:
        Navigator.pushReplacementNamed(context, '/history');
        break;
      default:
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Fitur belum tersedia')),
        );
    }
  }

  Color _roleColor(String role) {
    switch (role.toLowerCase()) {
      case 'admin':
      case 'administrator':
        return Colors.indigo;
      case 'kasir':
        return Colors.teal;
      default:
        return Colors.grey;
    }
  }

  @override
  Widget build(BuildContext context) {
    final isAdmin = _userRole == 2;
    final selectedIndex = isAdmin ? 3 : 0; // user menu di admin index 3

    return Scaffold(
      drawer: CustomSidebar(selectedIndex: selectedIndex, onItemSelected: _onSidebarItemSelected),
      appBar: AppBar(
        title: const Text('Kelola Pengguna'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadUsers,
            tooltip: 'Refresh',
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => _showFormDialog(),
        backgroundColor: const Color(0xFF6366f1),
        icon: const Icon(Icons.person_add, color: Colors.white),
        label: const Text('Tambah User', style: TextStyle(color: Colors.white)),
      ),
      body: Column(
        children: [
          Container(
            padding: const EdgeInsets.all(16),
            color: Colors.grey.shade50,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    const Icon(Icons.people, color: Color(0xFF6366f1)),
                    const SizedBox(width: 8),
                    const Text('Data Pengguna',
                        style: TextStyle(
                            fontSize: 16, fontWeight: FontWeight.bold)),
                    const Spacer(),
                    Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: const Color(0xFF6366f1).withOpacity(0.1),
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: Text(
                        '${_filteredUsers.length} user',
                        style: const TextStyle(
                            color: Color(0xFF6366f1),
                            fontWeight: FontWeight.w600,
                            fontSize: 12),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                TextField(
                  onChanged: (val) => setState(() => _searchQuery = val),
                  decoration: InputDecoration(
                    hintText: 'Cari nama, username, atau role...',
                    prefixIcon: const Icon(Icons.search, size: 20),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(10),
                      borderSide: BorderSide(color: Colors.grey.shade300),
                    ),
                    enabledBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(10),
                      borderSide: BorderSide(color: Colors.grey.shade300),
                    ),
                    filled: true,
                    fillColor: Colors.white,
                    contentPadding: const EdgeInsets.symmetric(
                        horizontal: 12, vertical: 10),
                    isDense: true,
                    suffixIcon: _searchQuery.isNotEmpty
                        ? IconButton(
                            icon: const Icon(Icons.clear, size: 18),
                            onPressed: () =>
                                setState(() => _searchQuery = ''),
                          )
                        : null,
                  ),
                ),
              ],
            ),
          ),
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : _error.isNotEmpty
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            const Icon(Icons.error_outline,
                                color: Colors.red, size: 48),
                            const SizedBox(height: 12),
                            Text(_error,
                                textAlign: TextAlign.center,
                                style: const TextStyle(color: Colors.red)),
                            const SizedBox(height: 16),
                            ElevatedButton.icon(
                              onPressed: _loadUsers,
                              icon: const Icon(Icons.refresh),
                              label: const Text('Coba Lagi'),
                            ),
                          ],
                        ),
                      )
                    : _filteredUsers.isEmpty
                        ? Center(
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(Icons.people_outline,
                                    size: 64,
                                    color: Colors.grey.shade400),
                                const SizedBox(height: 12),
                                Text(
                                  _searchQuery.isNotEmpty
                                      ? 'Tidak ada user yang cocok'
                                      : 'Belum ada user',
                                  style: TextStyle(
                                      color: Colors.grey.shade500,
                                      fontSize: 15),
                                ),
                              ],
                            ),
                          )
                        : RefreshIndicator(
                            onRefresh: _loadUsers,
                            child: ListView.builder(
                              padding: const EdgeInsets.fromLTRB(16, 8, 16, 80),
                              itemCount: _filteredUsers.length,
                              itemBuilder: (context, index) {
                                final user = _filteredUsers[index];
                                final nama = user['nama_lengkap'] ?? '-';
                                final username = user['username'] ?? '-';
                                final email = user['email'] ?? '-';
                                final noTlp = user['no_tlp'] ?? '-';
                                final role = user['nama_role'] ?? '-';
                                final id = user['id']?.toString() ?? '';

                                return Card(
                                  margin: const EdgeInsets.only(bottom: 10),
                                  shape: RoundedRectangleBorder(
                                      borderRadius: BorderRadius.circular(12)),
                                  elevation: 1,
                                  child: Padding(
                                    padding: const EdgeInsets.all(12),
                                    child: Row(
                                      children: [
                                        CircleAvatar(
                                          radius: 26,
                                          backgroundColor:
                                              _roleColor(role).withOpacity(0.15),
                                          child: Text(
                                            nama.isNotEmpty
                                                ? nama[0].toUpperCase()
                                                : '?',
                                            style: TextStyle(
                                              color: _roleColor(role),
                                              fontWeight: FontWeight.bold,
                                              fontSize: 20,
                                            ),
                                          ),
                                        ),
                                        const SizedBox(width: 12),
                                        Expanded(
                                          child: Column(
                                            crossAxisAlignment:
                                                CrossAxisAlignment.start,
                                            children: [
                                              Text(nama,
                                                  style: const TextStyle(
                                                      fontWeight:
                                                          FontWeight.bold,
                                                      fontSize: 15)),
                                              const SizedBox(height: 2),
                                              Text('@$username',
                                                  style: TextStyle(
                                                      color:
                                                          Colors.grey.shade600,
                                                      fontSize: 12)),
                                              const SizedBox(height: 4),
                                              Row(
                                                children: [
                                                  Container(
                                                    padding: const EdgeInsets
                                                        .symmetric(
                                                        horizontal: 8,
                                                        vertical: 2),
                                                    decoration: BoxDecoration(
                                                      color: _roleColor(role)
                                                          .withOpacity(0.1),
                                                      borderRadius:
                                                          BorderRadius.circular(
                                                              20),
                                                    ),
                                                    child: Text(
                                                      role,
                                                      style: TextStyle(
                                                          color:
                                                              _roleColor(role),
                                                          fontSize: 11,
                                                          fontWeight:
                                                              FontWeight.w600),
                                                    ),
                                                  ),
                                                  if (email != '-' &&
                                                      email.isNotEmpty) ...[
                                                    const SizedBox(width: 8),
                                                    Expanded(
                                                      child: Text(
                                                        email,
                                                        style: TextStyle(
                                                            fontSize: 11,
                                                            color: Colors
                                                                .grey.shade500),
                                                        overflow:
                                                            TextOverflow.ellipsis,
                                                      ),
                                                    ),
                                                  ],
                                                ],
                                              ),
                                            ],
                                          ),
                                        ),
                                        Column(
                                          children: [
                                            IconButton(
                                              icon: const Icon(Icons.edit,
                                                  color: Color(0xFF6366f1),
                                                  size: 20),
                                              onPressed: () =>
                                                  _showFormDialog(user: user),
                                              tooltip: 'Edit',
                                              constraints:
                                                  const BoxConstraints(),
                                              padding: const EdgeInsets.all(6),
                                            ),
                                            IconButton(
                                              icon: const Icon(Icons.delete,
                                                  color: Colors.red, size: 20),
                                              onPressed: () =>
                                                  _deleteUser(id, nama),
                                              tooltip: 'Hapus',
                                              constraints:
                                                  const BoxConstraints(),
                                              padding: const EdgeInsets.all(6),
                                            ),
                                          ],
                                        ),
                                      ],
                                    ),
                                  ),
                                );
                              },
                            ),
                          ),
          ),
        ],
      ),
    );
  }
}

// Dialog Form (sama seperti sebelumnya, tidak perlu diubah)
class _UserFormDialog extends StatefulWidget {
  final ApiService api;
  final Map<String, dynamic>? user;
  final VoidCallback onSuccess;

  const _UserFormDialog({
    required this.api,
    this.user,
    required this.onSuccess,
  });

  @override
  State<_UserFormDialog> createState() => _UserFormDialogState();
}

class _UserFormDialogState extends State<_UserFormDialog> {
  final _formKey = GlobalKey<FormState>();
  final _namaController = TextEditingController();
  final _usernameController = TextEditingController();
  final _passwordController = TextEditingController();
  final _emailController = TextEditingController();
  final _phoneController = TextEditingController();
  final _addressController = TextEditingController();

  int _selectedRole = 3;
  bool _isSubmitting = false;
  bool _obscurePassword = true;
  String _error = '';
  File? _selectedImage;
  final ImagePicker _picker = ImagePicker();

  bool get _isEdit => widget.user != null;

  @override
  void initState() {
    super.initState();
    if (_isEdit) {
      final u = widget.user!;
      _namaController.text = u['nama_lengkap'] ?? '';
      _usernameController.text = u['username'] ?? '';
      _emailController.text = u['email'] ?? '';
      _phoneController.text = u['no_tlp'] ?? '';
      _addressController.text = u['alamat'] ?? '';
      final roleName = (u['nama_role'] ?? '').toLowerCase();
      _selectedRole = roleName.contains('admin') ? 2 : 3;
    }
  }

  @override
  void dispose() {
    _namaController.dispose();
    _usernameController.dispose();
    _passwordController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    _addressController.dispose();
    super.dispose();
  }

  Future<void> _pickImage(ImageSource source) async {
    try {
      final XFile? picked = await _picker.pickImage(
        source: source,
        imageQuality: 80,
        maxWidth: 800,
      );
      if (picked != null) {
        setState(() => _selectedImage = File(picked.path));
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal memilih gambar: $e')),
        );
      }
    }
  }

  void _showImagePicker() {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
      ),
      builder: (_) => SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const SizedBox(height: 8),
            Container(
              width: 40,
              height: 4,
              decoration: BoxDecoration(
                  color: Colors.grey.shade300,
                  borderRadius: BorderRadius.circular(2)),
            ),
            const SizedBox(height: 12),
            const Text('Pilih Foto',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
            const SizedBox(height: 8),
            ListTile(
              leading: const Icon(Icons.camera_alt, color: Color(0xFF6366f1)),
              title: const Text('Kamera'),
              onTap: () {
                Navigator.pop(context);
                _pickImage(ImageSource.camera);
              },
            ),
            ListTile(
              leading: const Icon(Icons.photo_library,
                  color: Color(0xFF6366f1)),
              title: const Text('Galeri'),
              onTap: () {
                Navigator.pop(context);
                _pickImage(ImageSource.gallery);
              },
            ),
            if (_selectedImage != null)
              ListTile(
                leading: const Icon(Icons.delete, color: Colors.red),
                title: const Text('Hapus Foto',
                    style: TextStyle(color: Colors.red)),
                onTap: () {
                  Navigator.pop(context);
                  setState(() => _selectedImage = null);
                },
              ),
            const SizedBox(height: 8),
          ],
        ),
      ),
    );
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() {
      _isSubmitting = true;
      _error = '';
    });

    try {
      Response response;

      if (_isEdit) {
        response = await widget.api.updateUser(
          id: widget.user!['id'].toString(),
          namaLengkap: _namaController.text.trim(),
          username: _usernameController.text.trim(),
          password: _passwordController.text,
          email: _emailController.text.trim(),
          noTelp: _phoneController.text.trim(),
          alamat: _addressController.text.trim(),
          role: _selectedRole,
          imageFile: _selectedImage,
        );
      } else {
        response = await widget.api.createUser(
          namaLengkap: _namaController.text.trim(),
          username: _usernameController.text.trim(),
          password: _passwordController.text,
          email: _emailController.text.trim(),
          noTelp: _phoneController.text.trim(),
          alamat: _addressController.text.trim(),
          role: _selectedRole,
          imageFile: _selectedImage,
        );
      }

      final successCode = _isEdit ? 200 : 201;
      if (response.statusCode == successCode &&
          response.data['success'] == true) {
        if (mounted) {
          Navigator.pop(context);
          widget.onSuccess();
        }
      } else {
        String errorMsg =
            response.data['message'] ?? 'Gagal menyimpan user';
        final errors = response.data['errors'];
        if (errors != null && errors is Map) {
          errorMsg += ':\n' + (errors as Map).values.join('\n');
        }
        setState(() => _error = errorMsg);
      }
    } on DioException catch (e) {
      String errorMsg =
          e.response?.data['message'] ?? 'Koneksi gagal: ${e.message}';
      final errors = e.response?.data['errors'];
      if (errors != null && errors is Map) {
        errorMsg += ':\n' + (errors as Map).values.join('\n');
      }
      setState(() => _error = errorMsg);
    } catch (e) {
      setState(() => _error = 'Terjadi kesalahan: $e');
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Dialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      insetPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 24),
      child: SingleChildScrollView(
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: Form(
            key: _formKey,
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Icon(
                      _isEdit ? Icons.edit : Icons.person_add,
                      color: const Color(0xFF6366f1),
                    ),
                    const SizedBox(width: 8),
                    Text(
                      _isEdit ? 'Edit User' : 'Tambah User Baru',
                      style: const TextStyle(
                          fontSize: 17, fontWeight: FontWeight.bold),
                    ),
                    const Spacer(),
                    IconButton(
                      icon: const Icon(Icons.close),
                      onPressed: () => Navigator.pop(context),
                      padding: EdgeInsets.zero,
                      constraints: const BoxConstraints(),
                    ),
                  ],
                ),
                const Divider(height: 20),
                if (_error.isNotEmpty)
                  Container(
                    width: double.infinity,
                    margin: const EdgeInsets.only(bottom: 12),
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: Colors.red.shade50,
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: Colors.red.shade200),
                    ),
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Icon(Icons.error_outline,
                            color: Colors.red, size: 18),
                        const SizedBox(width: 6),
                        Expanded(
                          child: Text(_error,
                              style: const TextStyle(
                                  color: Colors.red, fontSize: 12)),
                        ),
                      ],
                    ),
                  ),
                Center(
                  child: GestureDetector(
                    onTap: _showImagePicker,
                    child: Stack(
                      children: [
                        CircleAvatar(
                          radius: 45,
                          backgroundColor: Colors.grey.shade200,
                          backgroundImage: _selectedImage != null
                              ? FileImage(_selectedImage!)
                              : null,
                          child: _selectedImage == null
                              ? Icon(Icons.person,
                                  size: 45, color: Colors.grey.shade400)
                              : null,
                        ),
                        Positioned(
                          bottom: 0,
                          right: 0,
                          child: Container(
                            padding: const EdgeInsets.all(5),
                            decoration: const BoxDecoration(
                              color: Color(0xFF6366f1),
                              shape: BoxShape.circle,
                            ),
                            child: const Icon(Icons.camera_alt,
                                color: Colors.white, size: 14),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 4),
                const Center(
                  child: Text('Foto Profil (opsional)',
                      style: TextStyle(fontSize: 11, color: Colors.grey)),
                ),
                const SizedBox(height: 16),
                TextFormField(
                  controller: _namaController,
                  decoration: const InputDecoration(
                    labelText: 'Nama Lengkap',
                    prefixIcon: Icon(Icons.person_outline, size: 20),
                    border: OutlineInputBorder(),
                    isDense: true,
                  ),
                  validator: (value) {
                    if (value?.trim().isEmpty == true)
                      return 'Nama lengkap wajib diisi';
                    if (value!.trim().length < 3)
                      return 'Nama minimal 3 karakter';
                    return null;
                  },
                ),
                const SizedBox(height: 10),
                DropdownButtonFormField<int>(
                  value: _selectedRole,
                  decoration: const InputDecoration(
                    labelText: 'Role',
                    prefixIcon: Icon(Icons.badge_outlined, size: 20),
                    border: OutlineInputBorder(),
                    isDense: true,
                  ),
                  items: const [
                    DropdownMenuItem(value: 3, child: Text('Kasir')),
                    DropdownMenuItem(value: 2, child: Text('Administrator')),
                  ],
                  onChanged: (value) {
                    if (value != null)
                      setState(() => _selectedRole = value);
                  },
                ),
                const SizedBox(height: 10),
                TextFormField(
                  controller: _usernameController,
                  decoration: const InputDecoration(
                    labelText: 'Username',
                    prefixIcon: Icon(Icons.alternate_email, size: 20),
                    border: OutlineInputBorder(),
                    isDense: true,
                  ),
                  validator: (value) {
                    if (value?.trim().isEmpty == true)
                      return 'Username wajib diisi';
                    if (value!.trim().length < 6)
                      return 'Username minimal 6 karakter';
                    return null;
                  },
                ),
                const SizedBox(height: 10),
                TextFormField(
                  controller: _passwordController,
                  obscureText: _obscurePassword,
                  decoration: InputDecoration(
                    labelText: _isEdit
                        ? 'Password (kosongkan jika tidak diubah)'
                        : 'Password',
                    prefixIcon: const Icon(Icons.lock_outline, size: 20),
                    border: const OutlineInputBorder(),
                    isDense: true,
                    suffixIcon: IconButton(
                      icon: Icon(
                        _obscurePassword
                            ? Icons.visibility_off
                            : Icons.visibility,
                        size: 18,
                      ),
                      onPressed: () => setState(
                          () => _obscurePassword = !_obscurePassword),
                    ),
                  ),
                  validator: (value) {
                    if (!_isEdit && (value?.isEmpty == true))
                      return 'Password wajib diisi';
                    if (value!.isNotEmpty && value.length < 6)
                      return 'Password minimal 6 karakter';
                    return null;
                  },
                ),
                const SizedBox(height: 10),
                TextFormField(
                  controller: _emailController,
                  keyboardType: TextInputType.emailAddress,
                  decoration: const InputDecoration(
                    labelText: 'Email (opsional)',
                    prefixIcon: Icon(Icons.email_outlined, size: 20),
                    border: OutlineInputBorder(),
                    isDense: true,
                  ),
                  validator: (value) {
                    final email = value?.trim() ?? '';
                    if (email.isEmpty) return null;
                    if (!RegExp(r'^[\w.-]+@[\w.-]+\.[a-zA-Z]{2,}$')
                        .hasMatch(email)) {
                      return 'Format email tidak valid';
                    }
                    return null;
                  },
                ),
                const SizedBox(height: 10),
                TextFormField(
                  controller: _phoneController,
                  keyboardType: TextInputType.phone,
                  decoration: const InputDecoration(
                    labelText: 'No Telepon (opsional)',
                    prefixIcon: Icon(Icons.phone_outlined, size: 20),
                    border: OutlineInputBorder(),
                    isDense: true,
                    hintText: '08xxxxxxxxxx',
                  ),
                  validator: (value) {
                    final phone = value?.trim() ?? '';
                    if (phone.isEmpty) return null;
                    if (!RegExp(r'^(\+62|62|08)[0-9]{8,13}$')
                        .hasMatch(phone)) {
                      return 'Format no telepon tidak valid';
                    }
                    return null;
                  },
                ),
                const SizedBox(height: 10),
                TextFormField(
                  controller: _addressController,
                  maxLines: 2,
                  decoration: const InputDecoration(
                    labelText: 'Alamat (opsional)',
                    prefixIcon: Icon(Icons.location_on_outlined, size: 20),
                    border: OutlineInputBorder(),
                    isDense: true,
                  ),
                  validator: (value) {
                    final alamat = value?.trim() ?? '';
                    if (alamat.isEmpty) return null;
                    if (alamat.length < 10) return 'Alamat minimal 10 karakter';
                    return null;
                  },
                ),
                const SizedBox(height: 20),
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton(
                        onPressed: () => Navigator.pop(context),
                        child: const Text('Batal'),
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      flex: 2,
                      child: ElevatedButton(
                        onPressed: _isSubmitting ? null : _submit,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF6366f1),
                          shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(8)),
                        ),
                        child: _isSubmitting
                            ? const SizedBox(
                                width: 18,
                                height: 18,
                                child: CircularProgressIndicator(
                                    color: Colors.white, strokeWidth: 2),
                              )
                            : Text(
                                _isEdit ? 'Simpan Perubahan' : 'Tambah User',
                                style: const TextStyle(
                                    color: Colors.white,
                                    fontWeight: FontWeight.w600),
                              ),
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