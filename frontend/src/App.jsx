import React, { useEffect, useState } from 'react';

// Vite define a pasta de publicacao; assim a API e os links PHP usam a mesma raiz.
const raiz = import.meta.env.BASE_URL.replace(/app\/?$/, '');
const API = raiz + 'api/index.php';
const paginasPHP = raiz + 'visao/';
const dataBR = valor => valor ? String(valor).slice(0, 10).split('-').reverse().join('/') : 'Não informada';
const moeda = valor => Number(valor || 0).toLocaleString('pt-BR', {style:'currency', currency:'BRL'});
const hora = valor => valor ? String(valor).slice(0, 5) : '--:--';
const turnoVazio = {empresa_id:'',data_turno:'',horario_inicio:'',horario_fim:'',funcao:'',descricao:'',valor:'',funcionarios:[]};
const lojaVazia = {nome:'',telefone:'',localizacao:'',cnpj:'',codigo_loja:''};
const experienciaVazia = {cargo:'',local:'',tempo:'',descricao:''};

function Voltar({onClick}) {return <button type="button" className="link" onClick={onClick}>← Voltar</button>;}
function Info({titulo,valor}) {return <div className="item-info"><strong>{titulo}</strong><p>{valor || 'Não cadastrado'}</p></div>;}

export default function App() {
  const [dados,setDados] = useState(null);
  const [erro,setErro] = useState('');
  const [aviso,setAviso] = useState('');
  const [tela,setTela] = useState('turnos');
  const [form,setForm] = useState(turnoVazio);
  const [editando,setEditando] = useState(null);
  const [carregando,setCarregando] = useState(false);
  const [email,setEmail] = useState('');
  const [senha,setSenha] = useState('');
  const [loja,setLoja] = useState(lojaVazia);
  const [codigoLoja,setCodigoLoja] = useState('');
  const [experiencia,setExperiencia] = useState(experienciaVazia);

  async function consultar() {
    try {
      const resposta = await fetch(API,{credentials:'include',cache:'no-store'});
      const json = await resposta.json();
      if (!resposta.ok) throw Error(json.erro || 'Não foi possível consultar o servidor.');
      setDados(json);
    } catch(e) {setErro(e.message || 'Falha ao conectar ao PHP.');setDados({usuario:null});}
  }
  useEffect(()=>{consultar();},[]);

  async function acao(nome,campos={}) {
    setErro('');setAviso('');setCarregando(true);
    try {
      const resposta = await fetch(API,{
        method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},
        body:JSON.stringify({acao:nome,csrf:dados?.csrf,...campos})
      });
      const texto = await resposta.text();
      let json;
      try {json=JSON.parse(texto);} catch {throw Error('A API PHP não retornou JSON. Confira a conexão com o banco e o Console.');}
      if (!resposta.ok) throw Error(json.erro || 'A operação não pôde ser concluída.');
      if (nome==='logout') {setDados({usuario:null});setTela('turnos');}
      else setDados(json);
      return true;
    } catch(e) {setErro(e.message);return false;}
    finally {setCarregando(false);}
  }
  async function executar(nome,valores,mensagem,proximaTela) {
    const ok=await acao(nome,valores);
    if(ok) {setAviso(mensagem);if(proximaTela)setTela(proximaTela);}
  }
  function navegar(destino) {setErro('');setAviso('');setTela(destino);}
  const dono = dados?.usuario?.tipo === 'dono';
  function criar() {setForm({...turnoVazio,empresa_id:String(dados.empresas?.[0]?.id||'')});setEditando(null);navegar('editar');}
  function editar(turno) {
    setForm({id:turno.id,empresa_id:String(turno.empresa_id),data_turno:turno.data_turno,
      horario_inicio:hora(turno.horario_inicio),horario_fim:turno.horario_fim?hora(turno.horario_fim):'',
      funcao:turno.funcao,descricao:turno.descricao,valor:turno.valor,funcionarios:[]});
    setEditando(turno.id);navegar('editar');
  }
  function mudarCampo(chave,valor) {setForm(f=>({...f,[chave]:valor}));}
  async function salvarTurno(e) {
    e.preventDefault();
    await executar(editando?'editar_turno':'criar_turno',form,editando?'Turno atualizado.':'Turno criado.','turnos');
  }

  if (!dados) return <div className="espera">Abrindo WorkFlow...</div>;
  if (!dados.usuario) return <main className="login"><section className="painel">
    <div className="marca">WorkFlow</div><h1>Entrar na sua conta</h1>
    {erro&&<p className="erro" role="alert">{erro}</p>}
    <form onSubmit={async e=>{e.preventDefault();await executar('login',{email,senha},'', 'turnos');}}>
      <label>E-mail<input type="email" autoComplete="username" value={email} onChange={e=>setEmail(e.target.value)} required/></label>
      <label>Senha<input type="password" autoComplete="current-password" value={senha} onChange={e=>setSenha(e.target.value)} required/></label>
      <button disabled={carregando} className="primario">Entrar</button>
    </form>
    <p><a href={paginasPHP+'EsqueciSenha.php'}>Esqueci minha senha</a></p>
    <p>Primeiro acesso? <a href={paginasPHP+'CadastroEmpresa.php'}>Cadastrar empresa</a> ou <a href={paginasPHP+'CadastroFreela.php'}>cadastrar freelancer</a>.</p>
    <small>Cadastros iniciais e recuperação de senha ainda utilizam páginas PHP.</small>
  </section></main>;

  const perfil = dados.perfil || {};
  const notificacoes = dados.notificacoes || [];
  const voltar = ()=>navegar(['nova_loja','solicitar_vinculo','nova_experiencia'].includes(tela)?'perfil':'turnos');
  const mudouPerfil = tela==='perfil'||['nova_loja','solicitar_vinculo','nova_experiencia'].includes(tela);
  return <div className="app">
    <nav className="navegacao" aria-label="Navegação principal">
      <strong className="logo">WorkFlow</strong>
      <button className={tela==='turnos'||tela==='editar'?'ativo':''} onClick={()=>navegar('turnos')} title="Turnos">▦ <span>Turnos</span></button>
      <button className={mudouPerfil?'ativo':''} onClick={()=>navegar('perfil')} title="Meu perfil">♙ <span>Meu perfil</span></button>
      {dono&&<button className={tela==='funcionarios'?'ativo':''} onClick={()=>navegar('funcionarios')} title="Funcionários">♟ <span>Funcionários</span></button>}
      <button className={tela==='notificacoes'?'ativo':''} onClick={()=>navegar('notificacoes')} title="Notificações">♧ <span>Notificações</span>{Number(dados.naoLidas)>0&&<b className="contador">{dados.naoLidas}</b>}</button>
      <button className={tela==='config'?'ativo':''} onClick={()=>navegar('config')} title="Configurações">⚙ <span>Configurações</span></button>
      <button onClick={()=>{if(window.confirm('Deseja sair da conta?'))acao('logout');}} title="Sair">⇥ <span>Sair</span></button>
    </nav>
    <main className="conteudo">
      <header className="topo"><div><small>{dono?'Painel do gerente':'Painel do freelancer'}</small><h1>Olá, {dados.usuario.nome}</h1></div></header>
      {erro&&<p className="erro" role="alert">{erro}</p>}
      {aviso&&<p className="aviso" role="status">{aviso}</p>}

      {tela==='config'&&<section className="painel"><Voltar onClick={voltar}/><h2>Configurações</h2><p>Esta página será implementada futuramente.</p></section>}
      {tela==='notificacoes'&&<section><Voltar onClick={voltar}/><h2>Notificações</h2>
        {!notificacoes.length&&<p>Nenhuma notificação por enquanto.</p>}
        {notificacoes.map(n=><article className={'painel '+(!Number(n.lida)?'destaque':'')} key={n.id}>
          <h3>{n.titulo}</h3><p>{n.mensagem}</p><small>{n.criada_em}</small>
          <div className="linha"><button onClick={()=>navegar('turnos')}>Ver turnos</button>{!Number(n.lida)&&<button disabled={carregando} onClick={()=>acao('marcar_lida',{id:n.id})}>Marcar como lida</button>}</div>
        </article>)}
      </section>}

      {tela==='turnos'&&dono&&<section><div className="linha"><h2>Turnos criados</h2><button className="primario" onClick={criar}>+ Novo turno</button></div>
        {!dados.turnos?.length&&<p>Nenhum turno cadastrado.</p>}
        <div className="cards">{(dados.turnos||[]).map(t=><article className="painel" key={t.id}>
          <small>{t.empresa_nome}</small><h3>{t.funcao}</h3>
          <p>{dataBR(t.data_turno)} · {hora(t.horario_inicio)} às {hora(t.horario_fim)}</p>
          <p>{moeda(t.valor)}</p><p>{t.descricao}</p><p>Vagas: {t.vagas_disponiveis} de {t.vagas_necessarias} disponíveis</p>
          <div className="linha"><button onClick={()=>editar(t)}>Editar</button>
          <button className="perigo" disabled={carregando} onClick={async()=>{if(window.confirm('Excluir turno e convites relacionados?')) await executar('excluir_turno',{id:t.id},'Turno excluído.');}}>Excluir</button></div>
        </article>)}</div>
      </section>}
      {tela==='editar'&&dono&&<section className="painel"><Voltar onClick={voltar}/><h2>{editando?'Editar turno':'Novo turno'}</h2>
        <form onSubmit={salvarTurno}>
          {!editando&&<label>Loja<select value={form.empresa_id} onChange={e=>setForm(f=>({...f,empresa_id:e.target.value,funcionarios:[]}))} required>
            <option value="">Selecione uma loja</option>{(dados.empresas||[]).map(e=><option key={e.id} value={e.id}>{e.nome} — {e.codigo_loja}</option>)}</select></label>}
          <label>Data<input type="date" value={form.data_turno} onChange={e=>mudarCampo('data_turno',e.target.value)} required/></label>
          <div className="campos-duplos"><label>Horário de entrada<input type="time" value={form.horario_inicio} onChange={e=>mudarCampo('horario_inicio',e.target.value)} required/></label>
            <label>Horário de saída<input type="time" value={form.horario_fim} onChange={e=>mudarCampo('horario_fim',e.target.value)} required/></label></div>
          <label>Função<input type="text" maxLength="50" value={form.funcao} onChange={e=>mudarCampo('funcao',e.target.value)} required/></label>
          <label>Valor (R$)<input type="number" min="0" step="0.01" value={form.valor} onChange={e=>mudarCampo('valor',e.target.value)} required/></label>
          <label>Descrição<textarea maxLength="150" value={form.descricao} onChange={e=>mudarCampo('descricao',e.target.value)} required/></label>
          {!editando?<fieldset><legend>Freelancers convidados</legend>
            {(dados.funcionarios||[]).filter(f=>String((dados.empresas||[]).find(e=>e.codigo_loja===f.codigo_loja)?.id)===String(form.empresa_id)).map(f=><label className="checagem" key={f.vinculo_id}>
              <input type="checkbox" checked={form.funcionarios.includes(Number(f.freelancer_id))} onChange={e=>mudarCampo('funcionarios',e.target.checked?[...form.funcionarios,Number(f.freelancer_id)]:form.funcionarios.filter(id=>id!==Number(f.freelancer_id)))}/>{f.nome}
            </label>)}
            {!(dados.funcionarios||[]).some(f=>String((dados.empresas||[]).find(e=>e.codigo_loja===f.codigo_loja)?.id)===String(form.empresa_id))&&<p>Aprove freelancers para esta loja antes de criar o turno.</p>}
          </fieldset>:<p>Os convites existentes não são alterados nesta edição.</p>}
          <button disabled={carregando} className="primario">Salvar turno</button>
        </form>
      </section>}
      {tela==='turnos'&&!dono&&<section><h2>Convites de turnos</h2><div className="cards">
        {!(dados.pendentes||[]).length&&<p>Você não tem convites pendentes.</p>}
        {(dados.pendentes||[]).map(t=><article className="painel" key={t.id}><small>{t.empresa_nome}</small><h3>{t.funcao}</h3>
          <p>{dataBR(t.data_turno)} · {hora(t.horario_inicio)} às {hora(t.horario_fim)}</p><p>{moeda(t.valor)}</p><p>{t.descricao}</p>
          <div className="linha"><button disabled={carregando} className="primario" onClick={()=>acao('responder_turno',{id:t.id,status:'Confirmado'})}>Aceitar</button>
            <button disabled={carregando} className="perigo" onClick={()=>acao('responder_turno',{id:t.id,status:'Recusado'})}>Recusar</button></div>
        </article>)}
        </div><h2>Turnos respondidos</h2><div className="cards">{(dados.respondidos||[]).map((t,i)=><article className="painel" key={i}><h3>{t.funcao}</h3>
          <p>{t.empresa_nome} · {dataBR(t.data_turno)} · {hora(t.horario_inicio)} às {hora(t.horario_fim)}</p><strong>{t.status}</strong></article>)}</div>
      </section>}

      {tela==='perfil'&&<section><div className="linha"><h2>Meu perfil</h2></div>
        <article className="painel"><h3>{perfil.nome||dados.usuario.nome}</h3>
          <div className="dados-perfil"><Info titulo="E-mail" valor={perfil.email}/><Info titulo="Telefone" valor={perfil.telefone}/>
          {dono?<><Info titulo="Cidade" valor={perfil.cidade}/><Info titulo="CNPJ" valor={perfil.cnpj}/></>:<Info titulo="CPF" valor={perfil.cpf}/>}</div>
        </article>
        {dono?<><div className="linha"><h2>Meus estabelecimentos</h2><button className="primario" onClick={()=>{setLoja(lojaVazia);navegar('nova_loja');}}>+ Nova empresa</button></div>
          <div className="cards">{(dados.empresas||[]).map(e=><article className="painel" key={e.id}><h3>{e.nome}</h3>
            <p><strong>Código:</strong> {e.codigo_loja}</p><p>{e.localizacao}</p><p>Telefone: {e.telefone}</p><p>CNPJ: {e.cnpj}</p></article>)}</div>
          {!dados.empresas?.length&&<p>Nenhuma empresa cadastrada.</p>}
        </>:<><div className="linha"><h2>Empresas com quem trabalho</h2><button className="primario" onClick={()=>{setCodigoLoja('');navegar('solicitar_vinculo');}}>+ Solicitar vínculo</button></div>
          <div className="cards">{(dados.empresasAprovadas||[]).map(e=><article className="painel" key={e.id}><h3>{e.nome}</h3><p>{e.localizacao}</p><p>Código: {e.codigo_loja}</p><strong>Vínculo aprovado</strong></article>)}</div>
          {!dados.empresasAprovadas?.length&&<p>Nenhuma empresa aprovada até o momento.</p>}
          <h3>Solicitações pendentes</h3>{!(dados.empresasPendentes||[]).length?<p>Nenhuma solicitação pendente.</p>:(dados.empresasPendentes||[]).map(e=><article className="painel" key={e.id}><strong>{e.nome}</strong><p>Aguardando confirmação do dono.</p></article>)}
          <div className="linha"><h2>Experiências profissionais</h2><button className="primario" onClick={()=>{setExperiencia(experienciaVazia);navegar('nova_experiencia');}}>+ Nova experiência</button></div>
          {!(dados.experiencias||[]).length?<p>Nenhuma experiência registrada.</p>:(dados.experiencias||[]).map(x=><article className="painel" key={x.id}><h3>{x.cargo}</h3><small>Duração: {x.tempo}</small><p>{x.descricao}</p></article>)}
        </>}
      </section>}

      {tela==='funcionarios'&&dono&&<section><h2>Funcionários</h2><h3>Freelancers confirmados</h3>
        {!(dados.funcionarios||[]).length?<p>Nenhum freelancer confirmado.</p>:(dados.funcionarios||[]).map(f=><article className="painel" key={f.vinculo_id}><h3>{f.nome}</h3><p>{f.email}</p><p>Telefone: {f.telefone||'Não cadastrado'}</p><small>Empresa: {f.empresa_nome} · Código: {f.codigo_loja}</small></article>)}
        <h3>Solicitações pendentes</h3>
        {!(dados.funcionariosPendentes||[]).length?<p>Nenhuma solicitação pendente.</p>:(dados.funcionariosPendentes||[]).map(f=><article className="painel" key={f.vinculo_id}><h3>{f.nome}</h3><p>{f.email}</p>
          <p>Empresa: {f.empresa_nome} · Código: {f.codigo_loja}</p>
          <div className="linha"><button className="primario" disabled={carregando} onClick={()=>executar('decidir_vinculo',{vinculo_id:f.vinculo_id,status:'Aprovado'},'Freelancer aprovado.')}>Aprovar</button>
            <button className="perigo" disabled={carregando} onClick={()=>{if(window.confirm('Recusar solicitação de vínculo?')) executar('decidir_vinculo',{vinculo_id:f.vinculo_id,status:'Recusado'},'Solicitação recusada.');}}>Recusar</button></div>
        </article>)}
      </section>}

      {tela==='nova_loja'&&dono&&<section className="painel"><Voltar onClick={voltar}/><h2>Cadastrar empresa</h2>
        <form onSubmit={async e=>{e.preventDefault();await executar('cadastrar_loja',loja,'Empresa cadastrada.','perfil');}}>
          <label>Nome<input required maxLength="100" value={loja.nome} onChange={e=>setLoja(v=>({...v,nome:e.target.value}))}/></label>
          <label>Telefone<input required maxLength="20" value={loja.telefone} onChange={e=>setLoja(v=>({...v,telefone:e.target.value}))}/></label>
          <label>Localização<input required maxLength="150" value={loja.localizacao} onChange={e=>setLoja(v=>({...v,localizacao:e.target.value}))}/></label>
          <label>CNPJ<input required maxLength="18" value={loja.cnpj} onChange={e=>setLoja(v=>({...v,cnpj:e.target.value}))}/></label>
          <label>Código para freelancers<input required maxLength="20" value={loja.codigo_loja} onChange={e=>setLoja(v=>({...v,codigo_loja:e.target.value}))}/></label>
          <button className="primario" disabled={carregando}>Cadastrar empresa</button>
        </form>
      </section>}
      {tela==='solicitar_vinculo'&&!dono&&<section className="painel"><Voltar onClick={voltar}/><h2>Solicitar vínculo com uma empresa</h2>
        <p>Informe o código fornecido pelo gerente. A solicitação ficará pendente até que ele aprove.</p>
        <form onSubmit={async e=>{e.preventDefault();await executar('solicitar_vinculo',{codigo_loja:codigoLoja},'Solicitação enviada.','perfil');}}>
          <label>Código da empresa<input required maxLength="20" value={codigoLoja} onChange={e=>setCodigoLoja(e.target.value)}/></label>
          <button className="primario" disabled={carregando}>Enviar solicitação</button>
        </form>
      </section>}
      {tela==='nova_experiencia'&&!dono&&<section className="painel"><Voltar onClick={voltar}/><h2>Registrar experiência</h2>
        <form onSubmit={async e=>{e.preventDefault();await executar('cadastrar_experiencia',experiencia,'Experiência registrada.','perfil');}}>
          <label>Função<input required maxLength="50" value={experiencia.cargo} onChange={e=>setExperiencia(v=>({...v,cargo:e.target.value}))}/></label>
          <label>Local<input required maxLength="100" value={experiencia.local} onChange={e=>setExperiencia(v=>({...v,local:e.target.value}))}/></label>
          <label>Duração<input required maxLength="30" value={experiencia.tempo} onChange={e=>setExperiencia(v=>({...v,tempo:e.target.value}))}/></label>
          <label>Descrição<textarea maxLength="250" value={experiencia.descricao} onChange={e=>setExperiencia(v=>({...v,descricao:e.target.value}))}/></label>
          <button className="primario" disabled={carregando}>Salvar experiência</button>
        </form>
      </section>}
    </main>
  </div>;
}
