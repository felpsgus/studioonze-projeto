<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Empresa\SaveRequest;
use App\Http\Resources\EmpresaResource;
use App\Models\Empresa;
use Illuminate\Http\Request;

class EmpresaController extends Controller {
	/**
	 * Display a listing of the resource.
	 */
	public function showAll() {
		$empresas = Empresa::all();
		$empresas->each(function ($empresa) {
			$empresa->logo = asset('storage/empresas/logos/' . $empresa->logo);
		});
		return EmpresaResource::collection($empresas);
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function save(Request $request) {
		$request->validate([
			'nome' => 'required|string',
			'cnpj' => 'required|string',
			'email' => 'email',
			'telefone' => 'string',
			'logo' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
		]);

		$fileName = null;
		if ($request->hasFile('logo')) {
			if (!$request->file('logo')->isValid()) {
				return response()->json([
					'message' => 'Arquivo inválido!'
				], 401);
			}

			$fileName = time() . '.' . $request->file('logo')->getClientOriginalExtension();
			$request->file('logo')->storeAs('public/empresas/logos', $fileName);
		} else {
			$fileName = null;
		}

		if (Empresa::where('cnpj', $request['cnpj'])->first()) {
			return response()->json([
				'message' => 'Empresa já cadastrada!'
			], 401);
		}

		$empresa = array(
			'nome' => $request->input('nome'),
			'cnpj' => $request->input('cnpj'),
			'email' => $request->input('email'),
			'telefone' => $request->input('telefone'),
			'logo' => $fileName
		);
		$empresa = new Empresa($empresa);

		$empresa->save();

		return new EmpresaResource($empresa);
	}

	/**
	 * Display the specified resource.
	 */
	public function show($id) {
		$empresa = Empresa::where('id_empresa', $id)->first();
		return new EmpresaResource($empresa);
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, $id) {
		echo json_encode($request->all());
		$request->validate([
			'nome' => 'required|string',
			'cnpj' => 'required|string',
			'email' => 'email',
			'telefone' => 'string',
			'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
		]);

		$empresa = Empresa::findOrFail($id);

		$fileName = null;
		if ($request->hasFile('logo')) {
			$fileName = time() . '.' . $request->file('logo')->getClientOriginalExtension();
			$request->file('logo')->storeAs('public/logos', $fileName);
			$empresa->logo = $fileName;
		} else {
			$fileName = null;
		}

		$empresa->update($empresa);
		return new EmpresaResource($empresa);
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy($id) {
		$empresa = Empresa::findOrFail($id);
		$empresa->delete();
		return new EmpresaResource($empresa);
	}
}
