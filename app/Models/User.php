<?php

namespace App\Models;

use CodeIgniter\Model;


class User extends Model
{
    protected $table = 'tbl_user';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = false;
    protected $returnType = 'object';
    protected $useSoftDelete = false;
    protected $protectFields = true;
    protected $allowedFields = ['id', 'nama_lengkap', 'username', 'password', 
    'no_tlp', 'alamat', 'image', 'status', 'role_id', 'created_at', 'updated_at'];

    //tanggal
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = '';
    protected $updatedField = '';
    protected $deletedField = '';

    public function findAllDataWithRelation()
    {
        return $this->select('tbl_user.*,tbl_role.nama_role AS nama_role')

            ->join('tbl_role', 'tbl_role.id=tbl_user.role_id')
            ->where('tbl_user.nama_lengkap !=', 'administrator')
            ->findAll();
    }
}


