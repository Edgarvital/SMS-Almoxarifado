<?php

namespace App\Http\Controllers;

use App\Estoque;
use App\HistoricoStatus;
use App\ItemSolicitacao;
use App\Material;
use App\Notificacao;
use App\Recibo;
use App\Setor;
use App\Solicitacao;
use App\Unidade;
use App\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class SolicitacaoController extends Controller
{

    public function show()
    {
        //Verifica as solicitações tanto do admin como do solicitante normal
        if (Auth::user()->cargo_id == 3) {
            $unidades = Unidade::where('usuario_id', Auth::user()->id)->first();
            $solicitacaos = Solicitacao::where('unidade_id', $unidades->id)->get()->toArray();
        } elseif (Auth::user()->cargo_id == 1) {
            $solicitacaos = Solicitacao::where('admin_id', Auth::user()->id)->get()->toArray();
            //Precisamos preencher para que o código funcione
            $unidades = Unidade::all();
        }

        $solicitacaos = array_column($solicitacaos, 'id');
        $historicoStatus = HistoricoStatus::whereIn('solicitacao_id', $solicitacaos)->where('status', 'Não Finalizado')->first();
        $itensSolicitacao = [];

        //Verifica se já existe uma solicitação não finalizada, se não existir, cria uma
        if ($historicoStatus != null) {
            $solicitacao = Solicitacao::find($historicoStatus->solicitacao_id);
            $itensSolicitacao = DB::table('item_solicitacaos')
                ->where('solicitacao_id', $solicitacao->id)
                ->join('materials', 'item_solicitacaos.material_id', '=', 'materials.id')
                ->join('estoques', 'item_solicitacaos.material_id', '=', 'estoques.material_id')
                ->select('item_solicitacaos.id','materials.nome','item_solicitacaos.quantidade_solicitada','item_solicitacaos.material_id','materials.unidade','estoques.quantidade')
                ->get();
        } else {
            $solicitacao = new Solicitacao();
            if (Auth::user()->cargo_id == 3) {
                $solicitacao->unidade_id = $unidades->id;
            } elseif (Auth::user()->cargo_id == 1) {
                $solicitacao->admin_id = Auth::user()->id;
            }
            $solicitacao->save();

            $historicoStatus = new HistoricoStatus();
            $historicoStatus->solicitacao_id = $solicitacao->id;
            $historicoStatus->status = 'Não Finalizado';
            $historicoStatus->save();
        }

        //Verifica se já existem itens na solicitação para não exibir os materiais que já estão presentes naquela solicitação
        if (count($itensSolicitacao) > 0) {
            //Se existir, é por que já foi adicionado um item na solicitação, então a unidade já foi atrelada na solicitação
            if (Auth::user()->cargo_id == 1) {
                $unidades = Unidade::find($solicitacao->unidade_id);
            }
            $materiais_solicitacao = array_column($itensSolicitacao->toArray(), 'material_id');
            $materiais = DB::table('materials')
                ->join('estoques', 'materials.id', '=', 'estoques.material_id')
                ->whereNotIn('materials.id', $materiais_solicitacao)
                ->where('setor_id', '=', $unidades->setor_id)
                ->get();

        } else {
            //Se não existir itens na solicitação, então não existe uma unidade na solicitação no caso do administrador
            if (Auth::user()->cargo_id == 1) {
                //Esse material::all() é desnecessário (por causa do JS que recupera os materiais em tempo real), mas é preciso para rodar o código
                $materiais = Material::all();
            } else {
                //Se não, ele já vai pegar os materiais especificos do setor, já que o solicitante já tem uma unidade atrelada
                $materiais = DB::table('materials')
                    ->join('estoques', 'materials.id', '=', 'estoques.material_id')
                    ->where('setor_id', '=', $unidades->setor_id)
                    ->get();
            }
        }

        return view('solicitacao.solicita_material', compact('materiais', 'unidades', 'solicitacao', 'itensSolicitacao'));


    }

    public function addMaterial(Request $request)
    {
        $validator = Validator::make($request->all(), ItemSolicitacao::$rulesAdd, ItemSolicitacao::$messages)->validate();

        $solicitacao = Solicitacao::find($request->solicitacao_id);

        $itemSolicitacao = new ItemSolicitacao();
        $itemSolicitacao->quantidade_solicitada = $request->quantidade_solicitada;
        if (Auth::user()->cargo_id == 1) {
            if ($solicitacao->unidade_id == null) {
                $solicitacao->unidade_id = $request->unidade_id;
                $solicitacao->update();
            }

            $itemSolicitacao->quantidade_aprovada = $itemSolicitacao->quantidade_solicitada;
        }
        $itemSolicitacao->material_id = $request->material_id;
        $itemSolicitacao->solicitacao_id = $request->solicitacao_id;

        $itemSolicitacao->save();

        return redirect()->back()->with('success', 'Material adicionado com sucesso!');
    }


    public function removerMaterial($item_id)
    {
        $itemSolicitacao = ItemSolicitacao::find($item_id);
        $itemSolicitacao->delete();
        return redirect()->back()->with('success', 'Material removido com sucesso!');
    }

    public function editarMaterial(Request $request)
    {
        $validator = Validator::make($request->all(), ItemSolicitacao::$rulesEdit, ItemSolicitacao::$messages)->validate();

        $item = ItemSolicitacao::find($request->item_id);

        $item->quantidade_solicitada = $request->quantidade_solicitada;
        $item->update();

        return redirect()->back()->with('success', 'Material editado com sucesso!');
    }

    public function store(Request $request)
    {
        $usuario = Auth::user();

        $solicitacao = Solicitacao::find($request->solicitacao_id);
        $solicitacao->observacao_requerente = $request->observacao_requerente;
        $solicitacao->update();

        $historicoStatus = HistoricoStatus::where('solicitacao_id', $solicitacao->id)->first();
        if ($usuario->cargo_id == 1) {
            $historicoStatus->status = 'Aprovado';
            $historicoStatus->data_aprovado = now();
        } else {
            $historicoStatus->status = 'Aguardando Analise';
        }
        $historicoStatus->update();

        return redirect()->back()->with('success', 'Solicitação realizada com sucesso!');
    }

    public function listSolicitacoesAprovadas()
    {
        $consulta = DB::select('select status.status, status.created_at, status.solicitacao_id, u.nome
            from historico_statuses status, unidades u, solicitacaos soli
            where status.data_aprovado IS NOT NULL and status.data_finalizado IS NULL and status.solicitacao_id = soli.id
            and soli.unidade_id = u.id order by status.id desc');


        $solicitacoesID = array_column($consulta, 'solicitacao_id');
        $materiaisPreview = [];

        if (!empty($solicitacoesID)) {
            $materiaisPreview = $this->getMateriaisPreview($solicitacoesID);
        }

        return view('solicitacao.entrega_materiais', [
            'dados' => $consulta, 'materiaisPreview' => $materiaisPreview,
        ]);
    }

    public function listSolicitacoesRequerente()
    {
        $itensSolicitacao = ItemSolicitacao::all();
        $solicitacaosId = $itensSolicitacao->unique('solicitacao_id')->pluck('solicitacao_id');

        $unidade = Unidade::where('usuario_id', '=', Auth::user()->id)->first();
        $solicitacoes = Solicitacao::whereIn('id', $solicitacaosId)->where('unidade_id', '=', $unidade->id)->get();
        $historicoStatus = HistoricoStatus::whereIn('solicitacao_id', array_column($solicitacoes->toArray(), 'id'))->orderBy('id', 'desc')->get();

        $solicitacoesID = array_column($historicoStatus->toArray(), 'solicitacao_id');
        $materiaisPreview = [];

        if (!empty($solicitacoesID)) {
            $materiaisPreview = $this->getMateriaisPreview($solicitacoesID, 'solicitacao_id');
        }

        return view('solicitacao.minha_solicitacao_requerente', [
            'status' => $historicoStatus, 'materiaisPreview' => $materiaisPreview,
        ]);
    }

    public function listTodasSolicitacoes()
    {
        $consulta = DB::select('select status.status, status.created_at, status.solicitacao_id, u.nome
            from historico_statuses status, unidades u, solicitacaos soli
            where status.data_finalizado IS NOT NULL and status.solicitacao_id = soli.id
            and soli.unidade_id = u.id order by status.id desc');

        $solicitacoesID = array_column($consulta, 'solicitacao_id');
        $materiaisPreview = [];

        if (!empty($solicitacoesID)) {
            $materiaisPreview = $this->getMateriaisPreview($solicitacoesID);
        }

        return view('solicitacao.todas_solicitacao', [
            'dados' => $consulta, 'materiaisPreview' => $materiaisPreview,
        ]);
    }

    public function checkEntregarMateriais(Request $request)
    {
        if ('aprovar_entrega' == $request->action) {
            return $this->entregarMateriais($request->solicitacaoID);
        }
        if ('cancelar_entrega' == $request->action) {
            return $this->cancelarEntregaMataeriais($request->solicitacaoID);
        }
    }

    public function gerarRecibo($id)
    {
        $recibo = Recibo::find($id);
        $solicitante = $recibo->unidade->nome;
        $itens = explode('#', $recibo->itens);
        $itensEntregues = [];
        $itensTrocados = [];

        foreach ($itens as $item) {
            $substituicaoConfirm = Str::contains($item, 'Substituido');
            if($substituicaoConfirm == True){
                array_push($itensTrocados, explode(' , Substituido por: ', $item));
            }else{
                array_push($itensEntregues, $item);
            }
        }


        $dia = $recibo->created_at->format('d');
        $ano = $recibo->created_at->format('Y');

        if ($recibo->created_at->format('m') == '01') {
            $mes = 'Janeiro';
        } elseif ($recibo->created_at->format('m') == '02') {
            $mes = 'Fevereiro';
        } elseif ($recibo->created_at->format('m') == '03') {
            $mes = 'Março';
        } elseif ($recibo->created_at->format('m') == '04') {
            $mes = 'Abril';
        } elseif ($recibo->created_at->format('m') == '05') {
            $mes = 'Maio';
        } elseif ($recibo->created_at->format('m') == '06') {
            $mes = 'Junho';
        } elseif ($recibo->created_at->format('m') == '07') {
            $mes = 'Julho';
        } elseif ($recibo->created_at->format('m') == '08') {
            $mes = 'Agosto';
        } elseif ($recibo->created_at->format('m') == '09') {
            $mes = 'Setembro';
        } elseif ($recibo->created_at->format('m') == '10') {
            $mes = 'Outubro';
        } elseif ($recibo->created_at->format('m') == '11') {
            $mes = 'Novembro';
        } elseif ($recibo->created_at->format('m') == '12') {
            $mes = 'Dezembro';
        }

        $pdf = PDF::loadView('solicitacao.recibo', compact('itensEntregues','itensTrocados', 'dia', 'mes', 'ano', 'solicitante'));
        $nomePDF = 'Relatório_Materiais_Mais_Movimentados_Solicitação_Semana.pdf';
        return $pdf->setPaper('a4')->stream($nomePDF);
    }

    public function entregarTodosMateriais()
    {
        $historicos = HistoricoStatus::where('status', 'Aprovado')->get();

        $flag = 0;
        $errorMessage = [];
        foreach ($historicos as $historico) {
            $itens = ItemSolicitacao::where('solicitacao_id', '=', $historico->solicitacao_id)->where('quantidade_aprovada', '!=', null)->get();
            $materiaisID = array_column($itens->toArray(), 'material_id');
            $materiaisNome = Material::select('nome')->whereIn('id', $materiaisID)->get();
            $quantAprovadas = array_column($itens->toArray(), 'quantidade_aprovada');

            $estoque = Estoque::wherein('material_id', $materiaisID)->where('deposito_id', 1)->orderBy('material_id', 'asc')->get();

            $checkQuant = true;

            foreach ($itens as $item) {
                $estoqueItem = Estoque::where('material_id', $item->material_id)->where('deposito_id', 1)->first();
                $materialNome = Material::where('id', $item->material_id)->first();

                if (($estoqueItem->quantidade - $item->quantidade_aprovada) < 0) {
                    $checkQuant = false;
                    $message = $materialNome->nome . ' quantidade disponível(' . $estoqueItem->quantidade . ')' . ' - quantidade aprovada(' . $item->quantidade_aprovada . ')';
                    array_push($errorMessage, $message);
                }
            }

            if ($checkQuant) {
                $materiais = Material::all();
                $usuarios = Usuario::all();
                $flag = 1;

                for ($i = 0; $i < count($materiaisID); ++$i) {
                    DB::update('update estoques set quantidade = quantidade - ? where material_id = ? and deposito_id = 1', [$quantAprovadas[$i], $materiaisID[$i]]);

                    $material = $materiais->find($materiaisID[$i]);
                    $estoque = DB::table('estoques')->where('material_id', '=', $materiaisID[$i])->first();
                    if (($estoque->quantidade - $quantAprovadas[$i]) <= $material->quantidade_minima) {
                        foreach ($usuarios as $usuario) {
                            if ($usuario->cargo_id == 2) {
                                \App\Jobs\emailMaterialEsgotando::dispatch($usuario, $material);

                                $mensagem = $material->nome . ' em estado critico.';
                                $notificacao = new Notificacao();
                                $notificacao->mensagem = $mensagem;
                                $notificacao->usuario_id = $usuario->id;
                                $notificacao->material_id = $material->id;
                                $notificacao->material_quant = $estoque->quantidade;
                                $notificacao->visto = false;
                                $notificacao->save();
                            }
                        }
                    }
                }

                //Criação do recibo
                $lista = '';
                $solicitacao = Solicitacao::find($historico->solicitacao_id);
                $itensSubstituidos = ItemSolicitacao::where('solicitacao_id', $historico->solicitacao_id)->where('item_troca_id', "!=" , null)->get();
                $itensSubstitutos =  ItemSolicitacao::whereIn('id',$itensSubstituidos->pluck('item_troca_id'))->get();
                $itensEntregues = ItemSolicitacao::where('solicitacao_id', $historico->solicitacao_id)
                    ->where('item_troca_id', null)
                    ->whereNotIn('id',$itensSubstituidos->pluck('item_troca_id'))
                    ->get();

                foreach ($itensSubstituidos as $item) {
                    $itemSubstituto = $itensSubstitutos->find($item->item_troca_id);
                    $material = Material::find($item->material_id);
                    $materialSubstituto = Material::find($itemSubstituto->material_id);

                    $lista = $lista . $item->quantidade_solicitada . ' UNID ' . $material->nome .
                        ' , Substituido por: ' .
                        $itemSubstituto->quantidade_aprovada . ' UNID ' . $materialSubstituto->nome .'#';

                }

                foreach ($itensEntregues as $item) {
                    $material = Material::find($item->material_id);
                    $lista = $lista . $item->quantidade_aprovada . ' UNID ' . $material->nome . '#';

                }

                $recibo = new Recibo();
                $recibo->unidade_id = $solicitacao->unidade_id;
                $recibo->itens = $lista;
                $recibo->save();
                // Fim

                DB::update(
                    'update historico_statuses set status = ?, data_finalizado = now() where solicitacao_id = ?',
                    ['Entregue', $historico->solicitacao_id]
                );
            }
        }
        if ($flag == 0) {
            return redirect()->back()->with('error', $errorMessage);
        }
        return redirect()->back()->with(['success' => 'Material(is) entregue(s) com sucesso!']);
    }

    public function entregarMateriais($id)
    {
        $itens = ItemSolicitacao::where('solicitacao_id', '=', $id)->where('quantidade_aprovada', '!=', null)->get();
        $materiaisID = array_column($itens->toArray(), 'material_id');
        $materiaisNome = Material::select('nome')->whereIn('id', $materiaisID)->get();
        $quantAprovadas = array_column($itens->toArray(), 'quantidade_aprovada');

        $estoque = Estoque::wherein('material_id', $materiaisID)->where('deposito_id', 1)->orderBy('material_id', 'asc')->get();

        $checkQuant = true;
        $errorMessage = [];

        foreach ($itens as $item) {
            $estoqueItem = Estoque::where('material_id', $item->material_id)->where('deposito_id', 1)->first();
            $materialNome = Material::where('id', $item->material_id)->first();

            if (($estoqueItem->quantidade - $item->quantidade_aprovada) < 0) {
                $checkQuant = false;
                $message = $materialNome->nome . ' quantidade disponível(' . $estoqueItem->quantidade . ')' . ' - quantidade aprovada(' . $item->quantidade_aprovada . ')';
                array_push($errorMessage, $message);
            }
        }

        if ($checkQuant) {
            $materiais = Material::all();
            $usuarios = Usuario::all();

            for ($i = 0; $i < count($materiaisID); ++$i) {
                DB::update('update estoques set quantidade = quantidade - ? where material_id = ? and deposito_id = 1', [$quantAprovadas[$i], $materiaisID[$i]]);

                $material = $materiais->find($materiaisID[$i]);
                $estoque = DB::table('estoques')->where('material_id', '=', $materiaisID[$i])->first();
                if (($estoque->quantidade - $quantAprovadas[$i]) <= $material->quantidade_minima) {
                    foreach ($usuarios as $usuario) {
                        if ($usuario->cargo_id == 2) {
                            \App\Jobs\emailMaterialEsgotando::dispatch($usuario, $material);

                            $mensagem = $material->nome . ' em estado critico.';
                            $notificacao = new Notificacao();
                            $notificacao->mensagem = $mensagem;
                            $notificacao->usuario_id = $usuario->id;
                            $notificacao->material_id = $material->id;
                            $notificacao->material_quant = $estoque->quantidade;
                            $notificacao->visto = false;
                            $notificacao->save();
                        }
                    }
                }
            }

            //Criação do recibo
            $lista = '';
            $solicitacao = Solicitacao::find($id);
            $itens = ItemSolicitacao::where('solicitacao_id', $id)->get();
            foreach ($itens as $item) {
                $material = Material::find($item->material_id);
                $lista = $lista . $item->quantidade_aprovada . ' UNID ' . $material->nome . '#';

            }
            $recibo = new Recibo();
            $recibo->unidade_id = $solicitacao->unidade_id;
            $recibo->itens = $lista;
            $recibo->save();
            // Fim

            DB::update(
                'update historico_statuses set status = ?, data_finalizado = now() where solicitacao_id = ?',
                ['Entregue', $id]
            );

            return redirect()->back()->with('success', 'Material(is) entregue(s) com sucesso!');
        }
        return redirect()->back()->with('error', $errorMessage);
    }

    public function cancelarEntregaMataeriais($id)
    {
        DB::update(
            'update historico_statuses set status = ?, data_finalizado = now() where solicitacao_id = ?',
            ['Cancelado', $id]
        );

        return redirect()->back()->with('success', 'Material(is) cancelado(s) com sucesso!');
    }

    public function getItemSolicitacaoAdmin($id)
    {
        if (session()->exists('itemSolicitacoes')) {
            session()->forget('itemSolicitacoes');
        }
        $solicitacao = Solicitacao::find($id);

        $consulta = DB::select('select item.quantidade_solicitada, item.material_id, mat.nome, mat.descricao, mat.unidade, item.id, item.quantidade_solicitada, est.quantidade, item.solicitacao_id
            from item_solicitacaos item, materials mat, estoques est where item.solicitacao_id = ? and mat.id = item.material_id and est.material_id = item.material_id and est.deposito_id = 1 and item.item_troca_id IS NULL', [$id]);

        session(['itemSolicitacoes' => $consulta]);
        return json_encode(array($consulta,$solicitacao));


    }

    public function getItemTrocaAdmin($material_id, $solicitacao_id)
    {

        $solicitacao = Solicitacao::find($solicitacao_id);
        $unidade = Unidade::find($solicitacao->unidade_id);
        $itensSolicitacao = ItemSolicitacao::where('solicitacao_id', $solicitacao_id)->get();

        $estoque = DB::table('estoques')
            ->join('materials', 'estoques.material_id', '=', 'materials.id')
            ->where('setor_id', $unidade->setor_id)
            ->whereNotIn('material_id', $itensSolicitacao->pluck('material_id'))
            ->get();

        return json_encode($estoque);

    }

    public function getSolicitanteSolicitacao($id)
    {
        $consulta = DB::select('select uni.nome from solicitacaos soli, unidades uni where soli.id = ? and uni.id = soli.unidade_id', [$id]);

        return json_encode($consulta);
    }

    public function getMateriais($unidade_id)
    {
        $solicitacaos = Solicitacao::where('admin_id', Auth::user()->id)->pluck('id');
        $historicoStatus = HistoricoStatus::whereIn('solicitacao_id', $solicitacaos)->where('status', 'Não Finalizado')->first();
        $itensSolicitacao = [];


        //Verifica se já existe uma solicitação não finalizada, se não existir, cria uma
        if ($historicoStatus != null) {
            $solicitacao = Solicitacao::find($historicoStatus->solicitacao_id);
            $itensSolicitacao = ItemSolicitacao::where('solicitacao_id', $solicitacao->id)->get();
        }


        $materiais_solicitacao = array_column($itensSolicitacao->toArray(), 'material_id');

        $unidade = Unidade::find($unidade_id);

        $materiais = DB::table('materials')
            ->join('estoques', 'materials.id', '=', 'estoques.material_id')
            ->whereNotIn('materials.id', $materiais_solicitacao)
            ->where('setor_id', '=', $unidade->setor_id)
            ->get();


        return response()->json($materiais);
    }

    public function cancelarSolicitacaoReq($id)
    {
        $solicitacao = Solicitacao::find($id);
        $unidade = Unidade::find($solicitacao->unidade_id);
        $usuario_id = $unidade->usuario_id;

        if (Auth::user()->id != $usuario_id) {
            return redirect()->back();
        }

        $solicitacao = HistoricoStatus::select('data_finalizado')->where('solicitacao_id', $id)->get();

        if (is_null($solicitacao[0]->data_finalizado)) {
            DB::update(
                'update historico_statuses set status = ?, data_finalizado = now() where solicitacao_id = ?',
                ['Cancelado', $id]
            );

            return redirect()->back()->with('success', 'A solicitação foi cancelada.');
        }

        return redirect()->back()->with('error', 'A solicitação não pode ser cancelada pois já foi finalizada.');
    }

    public function getItemSolicitacaoRequerente($id)
    {

        $solicitacao = Solicitacao::find($id);
        $unidade = Unidade::find($solicitacao->unidade_id);
        $usuarioID = $unidade->usuario_id;


        if (Auth::user()->id != $usuarioID) {
            return json_encode('');
        }

        $consulta = DB::select('select item.quantidade_solicitada, item.quantidade_aprovada, mat.nome, mat.descricao
            from item_solicitacaos item, materials mat where item.solicitacao_id = ? and mat.id = item.material_id', [$id]);

        return json_encode($consulta);
    }

    public function getMateriaisPreview($solicitacoes_id)
    {
        $materiaisIDItem = ItemSolicitacao::select('material_id', 'solicitacao_id')->whereIn('solicitacao_id', $solicitacoes_id)->where('item_troca_id', null)->orderBy('solicitacao_id', 'desc')->get();
        $itensSolicitacaoID = array_values(array_unique(array_column($materiaisIDItem->toArray(), 'solicitacao_id')));

        $materiais = DB::select('select item.material_id, item.solicitacao_id, mat.nome
            from item_solicitacaos item, materials mat
            where item.solicitacao_id in (' . implode(',', $solicitacoes_id) . ') and item.material_id = mat.id and item.item_troca_id IS NULL');

        $materiaisPreview = [];
        $auxCountMaterial = 0;

        for ($i = 0; $i < count($itensSolicitacaoID); ++$i) {
            for ($b = 0; $b < count($materiais); ++$b) {
                if ($auxCountMaterial > 2) {
                    break;
                }
                if ($itensSolicitacaoID[$i] == $materiais[$b]->solicitacao_id) {
                    if ($auxCountMaterial > 0) {
                        $materiaisPreview[$i] .= ', ' . $materiais[$b]->nome;
                    } else {
                        array_push($materiaisPreview, $materiais[$b]->nome);
                    }
                    ++$auxCountMaterial;
                }
            }
            $auxCountMaterial = 0;
        }
        return $materiaisPreview;
    }

    public function listSolicitacoesAnalise()
    {
        $consulta = DB::select('select status.status, status.created_at, status.solicitacao_id, u.nome
            from historico_statuses status, unidades u, solicitacaos soli
            where status.data_aprovado IS NULL and status.data_finalizado IS NULL and status.solicitacao_id = soli.id
            and u.id = soli.unidade_id order by status.id desc');

        //dd($consulta);

        $solicitacoesID = array_column($consulta, 'solicitacao_id');
        $materiaisPreview = [];

        if (!empty($solicitacoesID)) {
            $materiaisPreview = $this->getMateriaisPreview($solicitacoesID);
        }

        return view('solicitacao.analise', [
            'dados' => $consulta, 'materiaisPreview' => $materiaisPreview,
        ]);
    }

    public function getObservacaoSolicitacao($id)
    {
        $solicitacao = Solicitacao::find($id);
        $unidade = Unidade::find($solicitacao->unidade_id);
        $usuario_id = $unidade->usuario_id;

        if (2 != Auth::user()->cargo_id && Auth::user()->id != $usuario_id) {
            return json_encode('');
        }

        $consulta = DB::select('select observacao_requerente, observacao_admin from solicitacaos where id = ?', [$id]);

        return json_encode($consulta);
    }

    public function ajaxListarSolicitacoesAnalise()
    {
        $consulta = DB::select('select status.status, status.created_at, status.solicitacao_id, u.nome
            from historico_statuses status, unidades u, solicitacaos soli
            where status.data_aprovado IS NULL and status.data_finalizado IS NULL and status.solicitacao_id = soli.id
            and u.id = soli.unidade_id order by status.id desc');


        $solicitacoesID = array_column($consulta, 'solicitacao_id');
        $materiaisPreview = [];

        if (!empty($solicitacoesID)) {
            $materiaisPreview = $this->getMateriaisPreview($solicitacoesID);
        }

        $output = array('dados' => $consulta, 'materiaisPreview' => $materiaisPreview);

        return response()->json($output);
    }

    public function checkAnaliseSolicitacao(Request $request)
    {
        $itemSolicitacaos = session('itemSolicitacoes');

        if ('nega' == $request->action) {
            return $this->checarNegarSolicitacao($request->observacaoAdmin, $request->solicitacaoID);
        }
        if ('aprova' == $request->action) {
            return $this->checarAprovarSolicitacao($itemSolicitacaos, $request->quantAprovada, $request->observacaoAdmin, $request->solicitacaoID);
        }
    }

    public function checarNegarSolicitacao($observacaoAdmin, $solicitacaoID)
    {
        if (is_null($observacaoAdmin)) {
            return redirect()->back()->withErrors('Informe o motivo de a solicitação ter sido negada!');
        }
        DB::update('update historico_statuses set status = ?, data_finalizado = now() where solicitacao_id = ?', ['Negado', $solicitacaoID]);
        DB::update('update solicitacaos set observacao_admin = ? where id = ?', [$observacaoAdmin, $solicitacaoID]);

        if (session()->exists('itemSolicitacoes')) {
            session()->forget('itemSolicitacoes');
        }
        if (session()->exists('status')) {
            session()->forget('status');
        }

        $solicitacao = Solicitacao::where('id', $solicitacaoID)->first();
        $usuario = Usuario::where('id', $solicitacao->usuario_id)->first();

        \App\Jobs\emailSolicitacaoNaoAprovada::dispatch($usuario, $solicitacao);

        return redirect()->back()->with('success', 'Solicitação cancelada com sucesso!');
    }

    public function checarAprovarSolicitacao($itemSolicitacaos, $quantMateriais, $observacaoAdmin, $solicitacaoID)
    {
        $itensID = [];
        $materiaisID = [];
        $quantMatAprovados = [];
        $auxMateriaisRepetidos = [];
        $errorMessage = [];

        $checkInputVazio = 0;
        $checkQuantMinima = 0;
        $checkQuantAprovada = 0;

        if (count($itemSolicitacaos) != count($quantMateriais)) {
            return redirect()->back()->with('inputNULL', 'Informe os valores das quantidades aprovadas!');
        }

        //Verifica todos os materiais da solicitaçõa. Caso todos os campos estejam em branco ou com valor negativo retorna um erro.
        for ($i = 0; $i < count($itemSolicitacaos); ++$i) {
            if (empty($quantMateriais[$i])) {
                ++$checkInputVazio;
            } elseif (!empty($quantMateriais[$i]) && $quantMateriais[$i] < 0) {
                return redirect()->back()->with('inputNULL', 'Informe valores positivos para as quantidades aprovadas!');
            } else {
                //Cada material é adicionado ao auxMateriaisRepetidos.
                //Caso o material já esteja inserido nesse array a quantidade do material repetido é adicionada ao que está no array.
                if (array_key_exists($itemSolicitacaos[$i]->material_id, $auxMateriaisRepetidos)) {
                    $auxMateriaisRepetidos[$itemSolicitacaos[$i]->material_id] += $quantMateriais[$i];
                } elseif (!array_key_exists($itemSolicitacaos[$i]->material_id, $auxMateriaisRepetidos)) {
                    $auxMateriaisRepetidos[$itemSolicitacaos[$i]->material_id] = $quantMateriais[$i];
                }

                if ($auxMateriaisRepetidos[$itemSolicitacaos[$i]->material_id] <= $itemSolicitacaos[$i]->quantidade) {
                    array_push($itensID, $itemSolicitacaos[$i]->id);
                    array_push($materiaisID, $itemSolicitacaos[$i]->material_id);
                    array_push($quantMatAprovados, $quantMateriais[$i]);
                    if ($quantMateriais[$i] < $itemSolicitacaos[$i]->quantidade_solicitada) {
                        ++$checkQuantAprovada;
                    }
                } else {
                    ++$checkQuantMinima;
                    array_push($errorMessage, $itemSolicitacaos[$i]->nome . '(Dispoível:' . $itemSolicitacaos[$i]->quantidade . ')');
                }
            }
        }
        if ($checkInputVazio == count($itemSolicitacaos)) {
            return redirect()->back()->with('inputNULL', 'Informe os valores das quantidades aprovadas!');
        }
        if ($checkQuantMinima > 0) {
            return redirect()->back()->withErrors($errorMessage);
        }
        for ($i = 0; $i < count($itensID); ++$i) {
            DB::update('update item_solicitacaos set quantidade_aprovada = ? where id = ?', [$quantMatAprovados[$i], $itensID[$i]]);
        }

        DB::update(
            'update historico_statuses set status = ?, data_aprovado = now() where solicitacao_id = ?',
            [0 == $checkInputVazio && 0 == $checkQuantAprovada ? 'Aprovado' : 'Aprovado Parcialmente', $solicitacaoID]
        );

        DB::update('update solicitacaos set observacao_admin = ? where id = ?', [$observacaoAdmin, $solicitacaoID]);

        if (session()->exists('itemSolicitacoes')) {
            session()->forget('itemSolicitacoes');
        }
        if (session()->exists('status')) {
            session()->forget('status');
        }

        $solicitacao = Solicitacao::where('id', $solicitacaoID)->first();
        $usuario = Usuario::where('id', $solicitacao->usuario_id)->first();

        // Email de aprovação da solicitação
        // \App\Jobs\emailSolicitacaoAprovada::dispatch($usuario, $solicitacao);

        return redirect()->back()->with('success', 'Solicitação Aprovada com sucesso!');
    }


    public function realizarTroca(Request $request)
    {

        $itemSolicitacao = new ItemSolicitacao();
        $itemSolicitacao->solicitacao_id = $request->solicitacao_id;
        $itemSolicitacao->material_id = $request->itemSelicionado;
        $itemSolicitacao->quantidade_solicitada = $request->quant_material;
        $itemSolicitacao->quantidade_aprovada = $request->quant_material;
        $itemSolicitacao->save();

        $itemSolicitacaoOriginal = ItemSolicitacao::where('material_id', $request->itemAtual)->where('solicitacao_id', $request->solicitacao_id)->first();
        $itemSolicitacaoOriginal->item_troca_id = $itemSolicitacao->id;
        $itemSolicitacaoOriginal->quantidade_aprovada = 0;
        $itemSolicitacaoOriginal->update();

        return response()->json(['success' => 'Material Trocado com sucesso!']);

    }
}
