<?php

class User extends CI_Model
{
    public function save()
    {
        $data = [
            'email'    => $this->input->post('email'),
            'password' => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
        ];
        $result = $this->db->insert('users', $data);
        if ($result) {
            return [
                'id'      => $this->db->insert_id(),
                'status'  => true,
                'message' => 'Data successfully added',
            ];
        }
    }

    public function get_all($key = null, $value = null)
    {
        if ($key != null) {
            $query = $this->db->get_where('users', array($key => $value));
            return $query->result();
        }
        $query = $this->db->get('users');
        return $query->result();
    }

    public function is_valid()
    {
        $email    = $this->input->post('email');
        $password = $this->input->post('password');

        $hash = $this->get_all('email', $email)[0]->password;
        if (password_verify($password, $hash)) {
            return true;
        }

        return false;
    }

    public function update($id, $data)
    {
        $data = ["email" => $data->email];

        $this->db->where('id', $id);

        if ($this->db->update('users', $data)) {
            return [
                'status'  => true,
                'message' => 'Data successfully updated',
            ];
        }
    }

    public function delete($id)
    {
        $this->db->where('id', $id);

        if ($this->db->delete('users')) {
            return [
                'status'  => true,
                'message' => 'Data successfully deleted',
            ];
        }
    }
}
