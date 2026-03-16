@extends('admin.layouts.admin')

@section('page-title', 'Gerenciar Usuários')

@section('content')
<div class="admin-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-users"></i> Todos os Usuários</h5>
        <a href="{{ route('admin.users.create') }}" class="btn btn-success" style="border-radius: 50px; padding: 5px 15px;">
            <i class="fas fa-plus"></i> Novo Usuário
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Telefone</th>
                    <th>Tipo</th>
                    <th>Cadastro</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>
                        <strong>{{ $user->name }}</strong>
                    </td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->telefone ?? '-' }}</td>
                    <td>
                        @if($user->tipo === 'admin')
                            <span class="badge bg-danger">
                                <i class="fas fa-crown"></i> Admin
                            </span>
                        @else
                            <span class="badge bg-secondary">
                                <i class="fas fa-user"></i> Usuário
                            </span>
                        @endif
                    </td>
                    <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <a href="{{ route('admin.users.edit', $user->id) }}" 
                           class="btn btn-sm btn-primary" 
                           title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        
                        @if(auth()->id() != $user->id)
                        <form action="{{ route('admin.users.destroy', $user->id) }}" 
                              method="POST" 
                              class="d-inline"
                              onsubmit="return confirm('Tem certeza que deseja excluir este usuário?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" title="Excluir">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @else
                        <button class="btn btn-sm btn-secondary" disabled title="Você não pode excluir seu próprio usuário">
                            <i class="fas fa-trash"></i>
                        </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Nenhum usuário encontrado.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $users->links() }}
    </div>
</div>
@endsection